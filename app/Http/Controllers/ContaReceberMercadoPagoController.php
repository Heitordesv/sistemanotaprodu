<?php

namespace App\Http\Controllers;

use App\Models\ConfigEcommerce;
use App\Models\ContaReceber;
use App\Services\ContaReceberMercadoPagoDirectChargeService;
use App\Services\ContaReceberMercadoPagoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class ContaReceberMercadoPagoController extends Controller
{
    public function __construct(
        private ContaReceberMercadoPagoService $service,
        private ContaReceberMercadoPagoDirectChargeService $directChargeService
    ) {
    }

    public function pix(int $id)
    {
        return $this->executarAdmin($id, fn ($conta) => $this->directChargeService->gerarPix($conta));
    }

    public function boleto(int $id)
    {
        return $this->executarAdmin($id, fn ($conta) => $this->directChargeService->gerarBoleto($conta));
    }

    public function cartao(int $id)
    {
        return $this->executarAdmin($id, fn ($conta) => $this->service->gerarCartao($conta));
    }

    public function checkout(int $id)
    {
        return $this->executarAdmin($id, fn ($conta) => $this->service->gerarCheckout($conta));
    }

    public function status(int $id)
    {
        return $this->executarAdmin($id, fn ($conta) => $this->service->consultar($conta));
    }

    public function retorno(Request $request, int $id, string $token)
    {
        $conta = ContaReceber::where('id', $id)
            ->where('mercadopago_public_token', $token)
            ->firstOrFail();

        $paymentId = (string) ($request->query('payment_id') ?: $request->query('collection_id') ?: '');
        $resultado = $this->service->retornoPublico($conta, $paymentId ?: null);

        return view('conta_receber.mercadopago_retorno', [
            'conta' => $conta->fresh(),
            'resultado' => $resultado,
        ]);
    }

    public function webhook(Request $request, int $configId)
    {
        try {
            $config = ConfigEcommerce::findOrFail($configId);
            $type = (string) ($request->input('type') ?: $request->query('type') ?: '');
            $paymentId = (string) (
                data_get($request->all(), 'data.id')
                ?: $request->query('data.id')
                ?: $request->input('id')
                ?: ''
            );

            if ($type !== '' && $type !== 'payment') {
                return response()->json(['ok' => true], 200);
            }

            if ($paymentId === '') {
                return response()->json(['ok' => true], 200);
            }

            $this->validarAssinaturaSeDisponivel($request, $config, $paymentId);
            $this->service->processarWebhook($configId, $paymentId);

            return response()->json(['ok' => true], 200);
        } catch (\Throwable $e) {
            Log::warning('Falha no webhook Mercado Pago de Conta a Receber.', [
                'config_id' => $configId,
                'message' => $e->getMessage(),
            ]);

            if ($e instanceof RuntimeException && str_contains($e->getMessage(), 'assinatura')) {
                return response()->json(['ok' => false], 401);
            }

            // Não confirma um evento financeiro que não foi persistido. O 503
            // permite que o provedor reentregue a notificação depois de uma falha
            // temporária de banco, rede ou API.
            return response()->json(['ok' => false], 503);
        }
    }

    private function executarAdmin(int $id, callable $callback)
    {
        try {
            // Primeiro resolve a conta usando a sessão autenticada/tenant atual.
            $conta = $this->contaDaEmpresa($id);

            // A aprovação do Mercado Pago é um recebimento automático externo,
            // e não uma operação física de um caixa. O observer legado de
            // ContaReceber usa user_logged para descobrir o caixa; se a sessão
            // permanecesse ativa, o mesmo pagamento poderia cair no caixa de
            // quem consultou o status primeiro. Removemos somente durante a
            // chamada ao provedor e restauramos no finally, tornando webhook,
            // retorno público e consulta administrativa determinísticos.
            $session = session();
            $usuarioSessao = $session->get('user_logged');
            $session->forget('user_logged');

            try {
                $resultado = $callback($conta);
            } finally {
                if ($usuarioSessao !== null) {
                    $session->put('user_logged', $usuarioSessao);
                }
            }

            return response()->json($resultado);
        } catch (\Throwable $e) {
            report($e);

            return response()->json([
                'erro' => 'Não foi possível processar a operação do Mercado Pago.',
                'message' => 'Não foi possível processar a operação do Mercado Pago.',
            ], 422);
        }
    }

    private function contaDaEmpresa(int $id): ContaReceber
    {
        $user = session('user_logged');
        if (!$user) {
            throw new RuntimeException('Sessão expirada. Entre novamente no sistema.');
        }

        $empresaId = is_object($user)
            ? ($user->empresa_id ?? null)
            : ($user['empresa'] ?? $user['empresa_id'] ?? null);

        if (!$empresaId) {
            throw new RuntimeException('Empresa da sessão não identificada.');
        }

        return ContaReceber::where('id', $id)
            ->where('empresa_id', $empresaId)
            ->firstOrFail();
    }

    private function validarAssinaturaSeDisponivel(Request $request, ConfigEcommerce $config, string $paymentId): void
    {
        $secret = trim((string) ($config->mercadopago_webhook_secret ?? ''));
        if ($secret === '') {
            // Compatibilidade com configurações antigas: mesmo sem secret, a
            // conciliação ainda consulta o payment server-to-server com o Access
            // Token da empresa e valida empresa/conta pela external_reference.
            // O endpoint também possui rate limit. Novas configurações devem
            // manter o secret preenchido para autenticação criptográfica adicional.
            return;
        }

        $xSignature = trim((string) $request->header('x-signature'));
        $xRequestId = trim((string) $request->header('x-request-id'));

        if ($xSignature === '' || $xRequestId === '') {
            throw new RuntimeException('Webhook com assinatura ausente.');
        }

        // Usa o validador oficial quando a versão instalada do SDK o oferece.
        $validator = 'MercadoPago\\Webhook\\WebhookSignatureValidator';
        if (class_exists($validator)) {
            try {
                $validator::validate($xSignature, $xRequestId, $paymentId, $secret);
                return;
            } catch (\Throwable $e) {
                throw new RuntimeException('Webhook com assinatura inválida.');
            }
        }

        // Fallback compatível com o protocolo documentado pelo Mercado Pago:
        // x-signature = ts=<timestamp>,v1=<hmac_sha256>
        // manifest = id:<data.id>;request-id:<x-request-id>;ts:<ts>;
        $signatureParts = [];
        foreach (explode(',', $xSignature) as $part) {
            [$key, $value] = array_pad(explode('=', trim($part), 2), 2, null);
            if ($key !== null && $value !== null) {
                $signatureParts[trim($key)] = trim($value);
            }
        }

        $timestamp = (string) ($signatureParts['ts'] ?? '');
        $receivedHash = strtolower((string) ($signatureParts['v1'] ?? ''));

        if ($timestamp === '' || $receivedHash === '' || !ctype_digit($timestamp)) {
            throw new RuntimeException('Webhook com assinatura inválida.');
        }

        $manifest = 'id:' . strtolower($paymentId)
            . ';request-id:' . $xRequestId
            . ';ts:' . $timestamp . ';';

        $calculatedHash = hash_hmac('sha256', $manifest, $secret);

        if (!hash_equals($calculatedHash, $receivedHash)) {
            throw new RuntimeException('Webhook com assinatura inválida.');
        }
    }
}
