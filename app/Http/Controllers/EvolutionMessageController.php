<?php

namespace App\Http\Controllers;

use App\Helpers\CobrancaMensagemHelper;
use App\Jobs\SendEvolutionWhatsAppJob;
use App\Models\CobrancaAgentConfig;
use App\Models\ContaReceber;
use App\Models\Empresa;
use App\Models\EmpresaIntegracao;
use App\Models\OrdemServico;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class EvolutionMessageController extends Controller
{
    public function cobrar(Request $request, int $id)
    {
        $empresaId = $this->empresaId();
        $this->integracaoAtiva($empresaId);

        $conta = ContaReceber::where('empresa_id', $empresaId)
            ->where('id', $id)
            ->with(['cliente', 'venda.cliente'])
            ->first();

        if (!$conta) {
            return response()->json([
                'status' => 'erro',
                'mensagem' => 'Conta a receber não encontrada para esta empresa.',
            ], 404);
        }

        if ((int) $conta->status === 1 || (float) $conta->valor_recebido >= (float) $conta->valor_integral) {
            return response()->json([
                'status' => 'erro',
                'mensagem' => 'Esta conta já está recebida e não precisa ser cobrada.',
            ], 422);
        }

        // 1) Primeiro tenta localizar o cliente normal da conta/venda.
        $destinatario = $conta->getCliente();
        $clienteLogId = $destinatario ? (int) $destinatario->id : null;
        $empresaDestinatariaId = null;

        // 2) Se não existir cliente, usa empresa_id_emp para buscar na tabela empresas.
        if (!$destinatario && !empty($conta->empresa_id_emp)) {
            $destinatario = Empresa::find((int) $conta->empresa_id_emp);
            $empresaDestinatariaId = $destinatario ? (int) $destinatario->id : null;
            $clienteLogId = null; // Não gravar ID de empresa dentro de whatsapp_message_logs.cliente_id.
        }

        if (!$destinatario) {
            return response()->json([
                'status' => 'erro',
                'mensagem' => 'Não foi possível localizar o cliente nem a empresa vinculada a esta conta.',
            ], 422);
        }

        $numero = $this->telefoneDestinatario($destinatario);
        if (!$numero) {
            return response()->json([
                'status' => 'erro',
                'mensagem' => $empresaDestinatariaId
                    ? 'A empresa vinculada a esta conta não possui telefone/WhatsApp cadastrado.'
                    : 'O cliente não possui celular/WhatsApp cadastrado.',
            ], 422);
        }

        try {
            CobrancaMensagemHelper::garantirMensagensPadrao($empresaId);

            $config = CobrancaAgentConfig::where('empresa_id', $empresaId)->first();
            $mensagem = CobrancaMensagemHelper::montarCobranca($config, $conta, $destinatario);

            // Botão manual deve confirmar o envio real. Não depende do worker/cron.
            SendEvolutionWhatsAppJob::dispatchSync(
                $empresaId,
                $numero,
                $mensagem,
                'cobranca_manual',
                $clienteLogId,
                (int) $conta->id,
                null
            );
        } catch (\Throwable $e) {
            Log::error('Falha ao enviar cobrança manual pela Evolution', [
                'empresa_id' => $empresaId,
                'conta_id' => $conta->id,
                'cliente_id' => $clienteLogId,
                'empresa_destinataria_id' => $empresaDestinatariaId,
                'numero' => $numero,
                'erro' => $e->getMessage(),
            ]);

            return response()->json([
                'status' => 'erro',
                'mensagem' => 'Não foi possível enviar a cobrança: ' . $e->getMessage(),
            ], 422);
        }

        $nomeDestinatario = $destinatario->razao_social
            ?: ($destinatario->nome_fantasia ?: ($destinatario->nome ?? 'o destinatário'));

        return response()->json([
            'status' => 'sucesso',
            'mensagem' => 'Cobrança enviada para ' . $nomeDestinatario . '.',
            'provider' => 'Evolution API',
            'destinatario' => $empresaDestinatariaId ? 'empresa' : 'cliente',
        ]);
    }

    public function mensagemOs(Request $request, int $id)
    {
        $empresaId = $this->empresaId();
        $this->integracaoAtiva($empresaId);

        $ordem = OrdemServico::where('empresa_id', $empresaId)
            ->where('id', $id)
            ->with('cliente')
            ->firstOrFail();

        $cliente = $ordem->cliente;
        $numero = $this->telefoneDestinatario($cliente);

        if (!$numero) {
            return response()->json([
                'status' => 'erro',
                'mensagem' => 'O cliente desta OS não possui celular/WhatsApp cadastrado.',
            ], 422);
        }

        $request->validate([
            'mensagem' => 'nullable|string|max:3000',
        ]);

        $mensagem = CobrancaMensagemHelper::osAtualizacao(
            $ordem,
            $cliente,
            $request->input('mensagem')
        );

        try {
            SendEvolutionWhatsAppJob::dispatchSync(
                $empresaId,
                $numero,
                $mensagem,
                'ordem_servico_manual',
                (int) $cliente->id,
                null,
                (int) $ordem->id
            );
        } catch (\Throwable $e) {
            return response()->json([
                'status' => 'erro',
                'mensagem' => 'Não foi possível enviar a mensagem da OS: ' . $e->getMessage(),
            ], 422);
        }

        return response()->json([
            'status' => 'sucesso',
            'mensagem' => 'Mensagem da OS enviada para o cliente.',
            'provider' => 'Evolution API',
        ]);
    }

    public function previewOs(int $id)
    {
        $empresaId = $this->empresaId();

        $ordem = OrdemServico::where('empresa_id', $empresaId)
            ->where('id', $id)
            ->with('cliente')
            ->firstOrFail();

        if (!$ordem->cliente) {
            return response()->json([
                'status' => 'erro',
                'mensagem' => 'Cliente não encontrado para esta OS.',
            ], 422);
        }

        return response()->json([
            'status' => 'sucesso',
            'mensagem' => CobrancaMensagemHelper::osAtualizacao($ordem, $ordem->cliente),
            'numero' => $ordem->cliente->celular ?: $ordem->cliente->telefone,
        ]);
    }

    private function empresaId(): int
    {
        $sessao = session('user_logged');
        abort_unless($sessao && !empty($sessao['empresa']), 403);

        return (int) $sessao['empresa'];
    }

    private function integracaoAtiva(int $empresaId): EmpresaIntegracao
    {
        $integracao = EmpresaIntegracao::where('empresa_id', $empresaId)
            ->where('whatsapp_provider', 'evolution')
            ->where('whatsapp_ativo', true)
            ->first();

        abort_unless($integracao, 422, 'Evolution API não está configurada ou ativa para esta empresa.');

        return $integracao;
    }

    private function telefoneDestinatario($destinatario): ?string
    {
        if (!$destinatario) {
            return null;
        }

        $numero = preg_replace(
            '/\D/',
            '',
            (string) ($destinatario->celular ?: $destinatario->telefone)
        );

        return $numero !== '' ? $numero : null;
    }
}