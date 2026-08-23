<?php

namespace App\Http\Controllers;

use App\Jobs\EnviarConfirmaWhatsAppJobs;
use App\Models\Cliente;
use App\Models\ContaReceber;
use App\Models\ContaReceberPagamento;
use App\Services\ContaReceberPagamentoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use RuntimeException;
use Throwable;

class ContaReceberPagamentoController extends Controller
{
    public function pay($id)
    {
        $item = ContaReceber::findOrFail($id);

        if (!__valida_objeto($item)) {
            abort(403);
        }

        $pagamentos = ContaReceberPagamento::where('conta_receber_id', $item->id)
            ->where('status', 'confirmado')
            ->orderByDesc('data_pagamento')
            ->orderByDesc('id')
            ->get();

        $totalHistorico = round((float) $pagamentos->sum('valor'), 2);
        $recebidoAnteriorAoHistorico = round(max(0, (float) $item->valor_recebido - $totalHistorico), 2);
        $lotePagamento = (string) Str::uuid();
        $formasPagamento = $this->formasPagamentoRecebiveis();

        return view('conta_receber.pay', compact(
            'item',
            'pagamentos',
            'totalHistorico',
            'recebidoAnteriorAoHistorico',
            'lotePagamento',
            'formasPagamento'
        ));
    }

    public function payPut(Request $request, $id, ContaReceberPagamentoService $service)
    {
        $item = ContaReceber::findOrFail($id);

        if (!__valida_objeto($item)) {
            abort(403);
        }

        $formasValidas = array_keys($this->formasPagamentoRecebiveis());

        $validated = $request->validate([
            'data_recebimento' => ['required', 'date'],
            'lote_pagamento' => ['nullable', 'uuid'],
            'pagamentos' => ['required', 'array', 'min:1', 'max:10'],
            'pagamentos.*.forma_pagamento' => ['required', Rule::in($formasValidas)],
            'pagamentos.*.valor' => ['required'],
            'pagamentos.*.observacao' => ['nullable', 'string', 'max:500'],
        ], [
            'pagamentos.required' => 'Informe pelo menos uma forma de pagamento.',
            'pagamentos.*.forma_pagamento.required' => 'Selecione a forma de pagamento.',
            'pagamentos.*.forma_pagamento.in' => 'Forma de pagamento inválida para recebimento.',
            'pagamentos.*.valor.required' => 'Informe o valor de cada pagamento.',
            'data_recebimento.required' => 'Informe a data do recebimento.',
        ]);

        try {
            $antes = round((float) $item->valor_recebido, 2);

            $item = $service->registrarMultiplos(
                $item,
                $validated['pagamentos'],
                $validated['data_recebimento'],
                $validated['lote_pagamento'] ?? null
            );

            $idempotente = (bool) $item->getAttribute('recebimento_idempotente');
            $valorRegistrado = $idempotente
                ? 0.0
                : round(max(0, (float) $item->valor_recebido - $antes), 2);
            $restante = round(max(0, (float) $item->valor_integral - (float) $item->valor_recebido), 2);

            if ($valorRegistrado > 0) {
                $this->enviarConfirmacao($item, $valorRegistrado, $restante);
            }

            if ($idempotente) {
                $mensagem = 'Este recebimento já havia sido registrado. Nenhum valor foi duplicado.';
            } else {
                $mensagem = (int) $item->status === 1
                    ? 'Recebimento registrado. A conta foi quitada com sucesso.'
                    : 'Recebimento parcial registrado. Saldo restante: R$ ' . number_format($restante, 2, ',', '.');
            }

            return $this->redirectSeguroDepoisDoPagamento($request)
                ->with('flash_sucesso', $mensagem);
        } catch (RuntimeException $e) {
            Log::warning('Recebimento rejeitado por regra de negócio', [
                'conta_id' => $item->id,
                'empresa_id' => $item->empresa_id,
                'motivo' => $e->getMessage(),
            ]);

            return redirect()->back()
                ->withInput()
                ->with('flash_erro', $e->getMessage());
        } catch (Throwable $e) {
            Log::error('Erro interno ao registrar múltiplas formas em conta a receber', [
                'conta_id' => $item->id,
                'empresa_id' => $item->empresa_id,
                'exception' => $e,
            ]);

            return redirect()->back()
                ->withInput()
                ->with('flash_erro', 'Não foi possível registrar o recebimento. Tente novamente.');
        }
    }

    public function receberMassa(Request $request, ContaReceberPagamentoService $service)
    {
        $formasValidas = array_keys($this->formasPagamentoRecebiveis());

        $validated = $request->validate([
            'ids' => ['required'],
            'tipo_pagamento' => ['required', Rule::in($formasValidas)],
            'data_recebimento' => ['nullable', 'date'],
            'lote_pagamento' => ['nullable', 'uuid'],
        ], [
            'ids.required' => 'Selecione ao menos uma conta.',
            'tipo_pagamento.required' => 'Informe a forma de pagamento do recebimento em massa.',
            'tipo_pagamento.in' => 'Forma de pagamento inválida para recebimento.',
        ]);

        $ids = is_array($validated['ids'])
            ? $validated['ids']
            : explode(',', (string) $validated['ids']);

        $user = session('user_logged');
        $empresaId = (int) (is_object($user)
            ? ($user->empresa_id ?? 0)
            : ($user['empresa'] ?? $user['empresa_id'] ?? 0));

        if ($empresaId <= 0) {
            abort(403, 'Empresa da sessão não identificada.');
        }

        try {
            $resultado = $service->registrarMassa(
                $ids,
                $empresaId,
                $validated['tipo_pagamento'],
                $validated['data_recebimento'] ?? now()->toDateString(),
                $validated['lote_pagamento'] ?? (string) Str::uuid()
            );

            if ($resultado['idempotente']) {
                return redirect()->back()->with(
                    'flash_sucesso',
                    'Este lote de recebimento já havia sido processado. Nenhum valor foi duplicado.'
                );
            }

            $forma = ContaReceber::tiposPagamento()[$validated['tipo_pagamento']] ?? $validated['tipo_pagamento'];

            return redirect()->back()->with(
                'flash_sucesso',
                $resultado['quantidade'] . ' conta(s) recebida(s) via ' . $forma .
                '. Total: R$ ' . number_format($resultado['total'], 2, ',', '.')
            );
        } catch (RuntimeException $e) {
            Log::warning('Recebimento em massa rejeitado por regra de negócio', [
                'empresa_id' => $empresaId,
                'ids' => $ids,
                'motivo' => $e->getMessage(),
            ]);

            return redirect()->back()
                ->withInput()
                ->with('flash_erro', $e->getMessage());
        } catch (Throwable $e) {
            Log::error('Erro interno ao registrar recebimento em massa', [
                'empresa_id' => $empresaId,
                'ids' => $ids,
                'exception' => $e,
            ]);

            return redirect()->back()
                ->withInput()
                ->with('flash_erro', 'Não foi possível registrar os recebimentos. Tente novamente.');
        }
    }

    private function formasPagamentoRecebiveis(): array
    {
        return array_diff_key(
            ContaReceber::tiposPagamento(),
            array_flip(['06', '90'])
        );
    }

    private function redirectSeguroDepoisDoPagamento(Request $request)
    {
        $fallback = route('conta-receber.index');
        $destino = trim((string) $request->input('previous_url', ''));

        if ($destino === '') {
            return redirect()->to($fallback);
        }

        $partes = parse_url($destino);
        if ($partes === false) {
            return redirect()->to($fallback);
        }

        $scheme = strtolower((string) ($partes['scheme'] ?? ''));
        if ($scheme !== '' && !in_array($scheme, ['http', 'https'], true)) {
            return redirect()->to($fallback);
        }

        if (isset($partes['host'])) {
            if (strcasecmp((string) $partes['host'], $request->getHost()) !== 0) {
                return redirect()->to($fallback);
            }
        } elseif (!str_starts_with($destino, '/') || str_starts_with($destino, '//')) {
            return redirect()->to($fallback);
        }

        return redirect()->to($destino);
    }

    private function enviarConfirmacao(ContaReceber $item, float $valorRegistrado, float $restante): void
    {
        $cliente = Cliente::find($item->cliente_id);

        if (!$cliente) {
            return;
        }

        $telefone = $cliente->celular ?: $cliente->telefone;
        if (!$telefone) {
            return;
        }

        $numero = preg_replace('/\D/', '', (string) $telefone);
        if (!str_starts_with($numero, '55')) {
            $numero = '55' . $numero;
        }

        $nome = $cliente->razao_social ?: $cliente->nome_fantasia ?: 'Cliente';
        $valorFormatado = number_format($valorRegistrado, 2, ',', '.');

        if ((int) $item->status === 1) {
            $mensagem = "Olá, *{$nome}*! 👋\n\n";
            $mensagem .= "Recebemos *R$ {$valorFormatado}* referente ao título *{$item->referencia}*. ✅\n\n";
            $mensagem .= "Com este recebimento, a conta foi *quitada com sucesso*.\n\n";
            $mensagem .= "Agradecemos! 😊\n\n_" . config('app.name') . "_";
        } else {
            $restanteFormatado = number_format($restante, 2, ',', '.');
            $mensagem = "Olá, *{$nome}*! 👋\n\n";
            $mensagem .= "Recebemos *R$ {$valorFormatado}* referente ao título *{$item->referencia}*. 💰\n\n";
            $mensagem .= "Saldo restante: *R$ {$restanteFormatado}*.\n\n";
            $mensagem .= "Atenciosamente,\n_" . config('app.name') . "_";
        }

        EnviarConfirmaWhatsAppJobs::dispatch($numero, $mensagem, $item->empresa_id);
    }
}
