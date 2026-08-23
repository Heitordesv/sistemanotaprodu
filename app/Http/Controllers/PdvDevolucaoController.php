<?php

namespace App\Http\Controllers;

use App\Models\Usuario;
use App\Models\VendaCaixa;
use App\Services\PdvDevolucaoService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Throwable;

class PdvDevolucaoController extends Controller
{
    public function devolver(Request $request, int $id, PdvDevolucaoService $service)
    {
        $dados = $request->validate([
            'motivo' => 'required|string|min:5|max:255',
            'admin_id' => 'nullable|integer',
            'admin_senha' => 'nullable|string|max:100',
        ]);

        $empresaId = $this->empresaDaSessao($request);

        try {
            $resultado = $service->devolverNaoFiscal(
                $empresaId,
                $id,
                $dados['admin_id'] ?? null,
                $dados['admin_senha'] ?? null,
                $dados['motivo']
            );

            $this->registrarLog($empresaId, $id, 'devolucao_pdv');

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json($resultado, 200);
            }

            return redirect()->back()->with('flash_sucesso', $resultado['message']);
        } catch (ValidationException $e) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'message' => collect($e->errors())->flatten()->first() ?: 'Não foi possível concluir a devolução.',
                    'errors' => $e->errors(),
                ], 422);
            }

            throw $e;
        } catch (Throwable $e) {
            __saveLogError($e, $empresaId);

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'message' => 'Não foi possível concluir a devolução com segurança. Nenhuma nova tentativa deve ser feita sem consultar o status da operação.',
                ], 500);
            }

            return redirect()->back()->with('flash_erro', 'Não foi possível concluir a devolução com segurança. Consulte o histórico da operação.');
        }
    }

    public function cancelarNfce(Request $request, PdvDevolucaoService $service)
    {
        $dados = $request->validate([
            'id' => 'required|integer',
            'empresa_id' => 'nullable|integer',
            'motivo' => 'required|string|min:15|max:255',
            'admin_id' => 'nullable|integer',
            'admin_senha' => 'nullable|string|max:100',
        ]);

        $empresaId = $this->empresaDaSessao($request);

        if (isset($dados['empresa_id']) && (int) $dados['empresa_id'] !== $empresaId) {
            abort(403, 'Empresa inválida para esta devolução.');
        }

        try {
            $resultado = $service->cancelarFiscal(
                $empresaId,
                (int) $dados['id'],
                $dados['admin_id'] ?? null,
                $dados['admin_senha'] ?? null,
                $dados['motivo']
            );

            if (!$resultado['ok']) {
                return response()->json(
                    $resultado['payload'],
                    (int) ($resultado['http_status'] ?? 422)
                );
            }

            $this->sincronizarXmlCancelado($empresaId, (int) $dados['id']);
            $this->registrarLog($empresaId, (int) $dados['id'], 'cancelamento_nfce_devolucao');

            return response()->json(
                $resultado['payload'],
                (int) ($resultado['http_status'] ?? 200)
            );
        } catch (ValidationException $e) {
            return response()->json([
                'message' => collect($e->errors())->flatten()->first() ?: 'Não foi possível cancelar esta NFC-e.',
                'errors' => $e->errors(),
            ], 422);
        } catch (Throwable $e) {
            __saveLogError($e, $empresaId);

            return response()->json([
                'message' => 'O cancelamento não pôde ser concluído localmente. Consulte o status da devolução antes de tentar novamente para evitar evento duplicado.',
            ], 500);
        }
    }

    private function empresaDaSessao(Request $request): int
    {
        $usuarioId = (int) get_id_user();
        $usuario = Usuario::query()
            ->where('id', $usuarioId)
            ->where('ativo', true)
            ->first();

        if (!$usuario || (int) $usuario->empresa_id <= 0) {
            abort(403, 'Usuário autenticado inválido para esta operação.');
        }

        $empresaId = (int) $usuario->empresa_id;
        if ((int) ($request->empresa_id ?? $empresaId) !== $empresaId) {
            abort(403, 'Empresa inválida para esta operação.');
        }

        return $empresaId;
    }

    private function sincronizarXmlCancelado(int $empresaId, int $vendaId): void
    {
        try {
            $venda = VendaCaixa::query()
                ->where('id', $vendaId)
                ->where('empresa_id', $empresaId)
                ->first();

            if (!$venda) {
                return;
            }

            $chave = preg_replace('/\D/', '', (string) $venda->chave);
            $arquivo = public_path('xml_nfce_cancelada/' . $chave . '.xml');

            if (strlen($chave) === 44 && is_file($arquivo)) {
                importaXmlSieg(file_get_contents($arquivo), $empresaId);
            }
        } catch (Throwable $e) {
            // A integração contábil é pós-processamento. O cancelamento homologado
            // e o ledger local não podem ser revertidos por falha desse envio.
            report($e);
        }
    }

    private function registrarLog(int $empresaId, int $vendaId, string $tipo): void
    {
        $sessao = session('user_logged');
        $logId = is_array($sessao) ? ($sessao['log_id'] ?? null) : ($sessao->log_id ?? null);

        if (!$logId) {
            return;
        }

        __saveLog([
            'tipo' => $tipo,
            'usuario_log_id' => $logId,
            'tabela' => 'venda_caixas',
            'registro_id' => $vendaId,
            'empresa_id' => $empresaId,
        ]);
    }
}
