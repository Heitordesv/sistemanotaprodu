<?php

namespace App\Http\Controllers;

use App\Models\Cidade;
use App\Models\Cliente;
use App\Models\ConfigEcommerce;
use App\Models\ContaReceber;
use App\Models\Empresa;
use App\Models\Payment;
use App\Models\Plano;
use App\Models\Usuario;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    public function index()
    {
        $empresaId = $this->empresaIdAtual();
        $empresa = Empresa::findOrFail($empresaId);

        if (!$empresa->planoEmpresa) {
            session()->flash('mensagem_erro', 'Defina um plano!');
            return redirect()->route('empresas.index');
        }

        return redirect()->route('payment.finish', [
            'empresa_id' => $empresaId,
        ]);
    }

    public function finish()
    {
        $empresaId = $this->empresaIdAtual();
        $empresa = Empresa::with('planoEmpresa.plano')->findOrFail($empresaId);
        $plano = $empresa->planoEmpresa;

        abort_unless($plano, 404, 'Plano da empresa não encontrado.');

        $planosDisponiveis = Plano::where('visivel', true)
            ->orderBy('valor')
            ->get();

        $selectedPlanoId = (int) request('plano_id', $plano->plano_id);
        $selectedPlano = $planosDisponiveis->firstWhere('id', $selectedPlanoId)
            ?: $planosDisponiveis->first();

        $mpConfig = ConfigEcommerce::where('empresa_id', 1)->first();
        $mpPublicKey = $mpConfig->mercadopago_public_key ?? '';

        $usuarioAtual = Usuario::find(get_id_user());
        $payerEmail = $usuarioAtual->email ?? $empresa->email ?? '';

        $assinaturaAtual = Payment::where('empresa_id', $empresa->id)
            ->where('plano_id', $plano->id)
            ->where('forma_pagamento', 'cartao')
            ->latest('id')
            ->first();

        $assinaturaAtiva = $assinaturaAtual
            && in_array($assinaturaAtual->status, ['authorized', 'pending', 'paused'], true)
            && !str_starts_with((string) $assinaturaAtual->transacao_id, 'pending_');

        return view('payment.finish', compact(
            'empresa',
            'plano',
            'planosDisponiveis',
            'selectedPlano',
            'selectedPlanoId',
            'mpPublicKey',
            'payerEmail',
            'assinaturaAtual',
            'assinaturaAtiva'
        ));
    }

    public function gerarPixMercadoPago($id)
    {
        $conta = ContaReceber::findOrFail($id);

        if ((int) $conta->status === 1) {
            return response()->json([
                'status_pago' => true,
                'message' => 'Pagamento já aprovado. Não é necessário gerar um novo PIX.',
            ]);
        }

        $cliente = Cliente::find($conta->cliente_id)
            ?: Empresa::find($conta->empresa_id_emp);

        if (!$cliente) {
            return response()->json(['erro' => 'Cliente ou empresa não encontrado para o título.'], 400);
        }

        $config = ConfigEcommerce::where('empresa_id', $conta->empresa_id)->first();
        if (!$config || empty($config->mercadopago_access_token)) {
            return response()->json([
                'erro' => "Token do Mercado Pago não configurado para a Empresa ID #{$conta->empresa_id}.",
            ], 400);
        }

        $nomeCompleto = trim($cliente->razao_social ?? $cliente->nome_fantasia ?? 'Cliente');
        $partesNome = explode(' ', $nomeCompleto, 2);
        $cpfCnpj = preg_replace('/\D/', '', $cliente->cpf_cnpj ?? '');

        $payer = [
            'email' => $cliente->email ?? 'cliente@exemplo.com',
            'first_name' => $partesNome[0] ?: 'Cliente',
            'last_name' => $partesNome[1] ?? ' ',
        ];

        if (in_array(strlen($cpfCnpj), [11, 14], true)) {
            $payer['identification'] = [
                'type' => strlen($cpfCnpj) === 11 ? 'CPF' : 'CNPJ',
                'number' => $cpfCnpj,
            ];
        }

        $dados = [
            'transaction_amount' => (float) $conta->valor_integral,
            'description' => "Pagamento referente ao título: {$conta->referencia}",
            'external_reference' => (string) $conta->id,
            'payment_method_id' => 'pix',
            'notification_url' => url("/mercadopago/notification?id={$conta->id}"),
            'payer' => $payer,
        ];

        $resultado = $this->mercadoPagoRequest(
            'POST',
            'https://api.mercadopago.com/v1/payments',
            $config->mercadopago_access_token,
            $dados,
            uniqid("pix_{$conta->id}_", true)
        );

        $pix = data_get($resultado, 'point_of_interaction.transaction_data');
        if (!$pix || empty($resultado['id'])) {
            Log::error('Erro ao gerar PIX de ContaReceber.', [
                'conta_id' => $conta->id,
                'response' => $resultado,
            ]);

            return response()->json([
                'erro' => $resultado['message'] ?? 'Falha ao gerar PIX.',
            ], 400);
        }

        $conta->update([
            'status' => 0,
            'observacao' => "PIX Mercado Pago gerado (ID: {$resultado['id']}).",
            'chave_pix' => $pix['qr_code'] ?? null,
            'mp_payment_id' => $resultado['id'],
        ]);

        return response()->json([
            'qr_code_base64' => $pix['qr_code_base64'] ?? null,
            'qr_code_text' => $pix['qr_code'] ?? null,
        ]);
    }

    public function linkPublico($id)
    {
        $conta = ContaReceber::find($id);
        abort_unless($conta, 404, 'Título de pagamento não encontrado.');

        return view('payment.public', ['id' => $id]);
    }

    public function linkPublicoJson($id)
    {
        $conta = ContaReceber::find($id);
        if (!$conta) {
            return response()->json([
                'erro' => 'Conta não encontrada.',
                'conta' => null,
                'cliente' => null,
                'config' => null,
                'pix_chave' => null,
                'pix_qr_base64' => null,
            ], 404);
        }

        $cliente = Cliente::find($conta->cliente_id);
        if (!$cliente) {
            return response()->json([
                'erro' => 'Cliente não encontrado.',
                'conta' => $conta,
                'cliente' => null,
                'config' => null,
                'pix_chave' => $conta->chave_pix ?? null,
                'pix_qr_base64' => $conta->pix_qr_base64 ?? null,
            ], 404);
        }

        $config = ConfigEcommerce::where('empresa_id', $conta->empresa_id)->first();

        return response()->json([
            'erro' => null,
            'conta' => $conta,
            'cliente' => $cliente,
            'config' => $config,
            'pix_chave' => $conta->chave_pix ?? null,
            'pix_qr_base64' => $conta->pix_qr_base64 ?? null,
        ]);
    }

    public function linkPublicoEmpresa($id)
    {
        $conta = ContaReceber::find($id);
        abort_unless($conta, 404, 'Título de pagamento não encontrado.');

        $empresa = Empresa::find($conta->empresa_id_emp);
        abort_unless($empresa, 404, 'Empresa não encontrada para esta conta.');

        return view('payment.empresa', [
            'id' => $id,
            'empresa' => $empresa,
            'conta' => $conta,
            'pago' => (int) $conta->status === 1,
        ]);
    }

    public function linkPublicoEmpresaJson($id)
    {
        $conta = ContaReceber::find($id);
        if (!$conta) {
            return response()->json([
                'erro' => 'Conta não encontrada.',
                'pago' => false,
                'conta' => null,
                'empresa' => null,
            ], 404);
        }

        $empresa = Empresa::find($conta->empresa_id_emp);
        if (!$empresa) {
            return response()->json([
                'erro' => 'Empresa não encontrada.',
                'pago' => false,
                'conta' => $conta,
                'empresa' => null,
            ], 404);
        }

        return response()->json([
            'erro' => null,
            'pago' => (int) $conta->status === 1,
            'conta' => $conta,
            'empresa' => $empresa,
        ]);
    }

    public function verificarStatus($id)
    {
        $conta = ContaReceber::find($id);
        if (!$conta) {
            return response()->json(['erro' => 'Conta não encontrada.'], 404);
        }

        if ((int) $conta->status === 1) {
            return response()->json([
                'status' => 1,
                'data_recebimento' => $conta->data_recebimento
                    ? Carbon::parse($conta->data_recebimento)->format('d/m/Y H:i')
                    : null,
                'nova_expiracao' => null,
            ]);
        }

        $config = ConfigEcommerce::where('empresa_id', $conta->empresa_id)->first();
        if (!$config || empty($config->mercadopago_access_token)) {
            return response()->json([
                'erro' => "Configuração Mercado Pago não encontrada para a Empresa ID #{$conta->empresa_id}.",
            ], 404);
        }

        $pagamento = null;

        if (!empty($conta->mp_payment_id)) {
            $pagamento = $this->mercadoPagoRequest(
                'GET',
                'https://api.mercadopago.com/v1/payments/' . urlencode((string) $conta->mp_payment_id),
                $config->mercadopago_access_token
            );
        } else {
            $resultado = $this->mercadoPagoRequest(
                'GET',
                'https://api.mercadopago.com/v1/payments/search?external_reference=' . urlencode((string) $conta->id),
                $config->mercadopago_access_token
            );

            $results = $resultado['results'] ?? [];
            if ($results) {
                usort($results, fn ($a, $b) => strcmp($b['date_created'] ?? '', $a['date_created'] ?? ''));
                $pagamento = $results[0] ?? null;
            }
        }

        if (!$pagamento) {
            return response()->json(['status' => 0]);
        }

        if (($pagamento['status'] ?? null) === 'approved') {
            $conta->update([
                'status' => 1,
                'valor_recebido' => $pagamento['transaction_amount'] ?? $conta->valor_integral,
                'data_recebimento' => $pagamento['date_approved'] ?? now(),
                'observacao' => 'Pagamento aprovado via Mercado Pago. ID: ' . ($pagamento['id'] ?? 'N/A'),
                'mp_payment_id' => $pagamento['id'] ?? $conta->mp_payment_id,
            ]);

            return response()->json([
                'status' => 1,
                'data_recebimento' => Carbon::parse($conta->fresh()->data_recebimento)->format('d/m/Y H:i'),
                'nova_expiracao' => null,
            ]);
        }

        return response()->json([
            'status' => 0,
            'mp_status' => $pagamento['status'] ?? 'pending',
        ]);
    }

    public function gerarBoletoEmpresaMercadoPago($id)
    {
        $conta = ContaReceber::findOrFail($id);
        $empresa = Empresa::find($conta->empresa_id_emp);

        if (!$empresa) {
            return response()->json(['erro' => 'Empresa pagadora não encontrada.'], 400);
        }

        $config = ConfigEcommerce::where('empresa_id', $conta->empresa_id)->first();
        if (!$config || empty($config->mercadopago_access_token)) {
            return response()->json(['erro' => 'Token do Mercado Pago não configurado.'], 400);
        }

        $cidade = $empresa->cidade_id ? Cidade::find($empresa->cidade_id) : null;
        $nome = trim($empresa->nome_fantasia ?? $empresa->razao_social ?? 'Empresa');
        $partes = explode(' ', $nome, 2);
        $documento = preg_replace('/\D/', '', $empresa->cpf_cnpj ?? '');

        $payer = [
            'email' => $empresa->email ?? 'empresa@exemplo.com',
            'first_name' => $partes[0] ?: 'Empresa',
            'last_name' => $partes[1] ?? ' ',
            'address' => [
                'zip_code' => preg_replace('/\D/', '', $empresa->cep ?? ''),
                'street_name' => $empresa->rua ?? 'Endereço não informado',
                'street_number' => $empresa->numero ?? 'S/N',
                'neighborhood' => $empresa->bairro ?? 'Bairro',
                'city' => $cidade->nome ?? 'Cidade',
                'federal_unit' => $cidade->uf ?? ($empresa->uf ?? 'SP'),
            ],
        ];

        if (in_array(strlen($documento), [11, 14], true)) {
            $payer['identification'] = [
                'type' => strlen($documento) === 11 ? 'CPF' : 'CNPJ',
                'number' => $documento,
            ];
        }

        $resultado = $this->mercadoPagoRequest(
            'POST',
            'https://api.mercadopago.com/v1/payments',
            $config->mercadopago_access_token,
            [
                'transaction_amount' => (float) $conta->valor_integral,
                'description' => "Pagamento referente ao título: {$conta->referencia}",
                'external_reference' => (string) $conta->id,
                'payment_method_id' => 'bolbradesco',
                'notification_url' => url("/mercadopago/notification?id={$conta->id}"),
                'payer' => $payer,
            ],
            uniqid("boleto_{$conta->id}_", true)
        );

        $boletoLink = data_get($resultado, 'transaction_details.external_resource_url');
        if (!$boletoLink || empty($resultado['id'])) {
            return response()->json([
                'erro' => $resultado['message'] ?? 'Erro ao gerar boleto.',
            ], 400);
        }

        $conta->update([
            'status' => 0,
            'boleto_link' => $boletoLink,
            'mp_payment_id' => $resultado['id'],
            'observacao' => 'Boleto Mercado Pago gerado.',
        ]);

        return response()->json([
            'boleto_link' => $boletoLink,
            'mp_payment_id' => $resultado['id'],
        ]);
    }

    public function verificarPagamentoMercadoPago($id)
    {
        $conta = ContaReceber::findOrFail($id);

        if (empty($conta->mp_payment_id)) {
            return response()->json(['erro' => 'Payment ID não encontrado.'], 400);
        }

        $config = ConfigEcommerce::where('empresa_id', $conta->empresa_id)->first();
        if (!$config || empty($config->mercadopago_access_token)) {
            return response()->json(['erro' => 'Token do Mercado Pago não configurado.'], 400);
        }

        $resultado = $this->mercadoPagoRequest(
            'GET',
            'https://api.mercadopago.com/v1/payments/' . urlencode((string) $conta->mp_payment_id),
            $config->mercadopago_access_token
        );

        if (($resultado['status'] ?? null) === 'approved') {
            if ((int) $conta->status !== 1) {
                $conta->update([
                    'status' => 1,
                    'valor_recebido' => $resultado['transaction_amount'] ?? $conta->valor_integral,
                    'data_recebimento' => $resultado['date_approved'] ?? now(),
                    'observacao' => 'Pagamento aprovado via Mercado Pago.',
                ]);
            }

            return response()->json([
                'status' => 1,
                'data_recebimento' => Carbon::parse($conta->fresh()->data_recebimento)->format('d/m/Y H:i'),
            ]);
        }

        return response()->json([
            'status' => $resultado['status'] ?? 'pending',
        ]);
    }

    private function empresaIdAtual(): int
    {
        $empresaId = (int) (request()->empresa_id ?: session('user_logged.empresa'));
        abort_unless($empresaId > 0, 401, 'Empresa não identificada na sessão.');

        return $empresaId;
    }

    private function mercadoPagoRequest(
        string $method,
        string $url,
        string $accessToken,
        array $payload = [],
        ?string $idempotencyKey = null
    ): array {
        $curl = curl_init();
        $headers = [
            'accept: application/json',
            'content-type: application/json',
            'Authorization: Bearer ' . trim($accessToken),
        ];

        if ($idempotencyKey) {
            $headers[] = 'X-Idempotency-Key: ' . $idempotencyKey;
        }

        $options = [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => strtoupper($method),
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_CONNECTTIMEOUT => 8,
            CURLOPT_TIMEOUT => 20,
        ];

        if ($payload && strtoupper($method) !== 'GET') {
            $options[CURLOPT_POSTFIELDS] = json_encode($payload);
        }

        curl_setopt_array($curl, $options);
        $body = curl_exec($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $curlError = curl_error($curl);
        curl_close($curl);

        if ($body === false || $curlError) {
            return ['message' => 'Falha de comunicação com o Mercado Pago.'];
        }

        $data = json_decode($body, true);
        if (!is_array($data)) {
            return ['message' => "Resposta inválida do Mercado Pago (HTTP {$httpCode})."];
        }

        if ($httpCode >= 400 && empty($data['message'])) {
            $data['message'] = "Mercado Pago retornou HTTP {$httpCode}.";
        }

        return $data;
    }
}