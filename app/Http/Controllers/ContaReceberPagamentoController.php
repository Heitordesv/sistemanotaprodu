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
        $formasPagamento = ContaReceber::tiposPagamento();

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

        $formasValidas = array_keys(ContaReceber::tiposPagamento());

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
            'pagamentos.*.forma_pagamento.in' => 'Forma de pagamento inválida.',
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

            $valorRegistrado = round(max(0, (float) $item->valor_recebido - $antes), 2);
            $restante = round(max(0, (float) $item->valor_integral - (float) $item->valor_recebido), 2);

            if ($valorRegistrado > 0) {
                $this->enviarConfirmacao($item, $valorRegistrado, $restante);
            }

            $mensagem = (int) $item->status === 1
                ? 'Recebimento registrado. A conta foi quitada com sucesso.'
                : 'Recebimento parcial registrado. Saldo restante: R$ ' . number_format($restante, 2, ',', '.');

            return redirect($request->input('previous_url', route('conta-receber.index')))
                ->with('flash_sucesso', $mensagem);
        } catch (\Throwable $e) {
            Log::error('Erro ao registrar múltiplas formas em conta a receber', [
                'conta_id' => $item->id,
                'empresa_id' => $item->empresa_id,
                'erro' => $e->getMessage(),
            ]);

            return redirect()->back()
                ->withInput()
                ->with('flash_erro', $e->getMessage());
        }
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