<?php

namespace App\Http\Controllers\API;

use App\Helpers\StockMove;
use App\Http\Controllers\Controller;
use App\Jobs\EnviarMensagemWhatsAppVenda;
use App\Models\CategoriaConta;
use App\Models\ComissaoVenda;
use App\Models\ContaReceber;
use App\Models\Empresa;
use App\Models\FaturaFrenteCaixa;
use App\Models\Funcionario;
use App\Models\ItemVendaCaixa;
use App\Models\OrdemServico;
use App\Models\Pedido;
use App\Models\Produto;
use App\Models\VendaCaixa;
use App\Models\VendaCaixaPreVenda;
use App\Services\LimiteCreditoClienteService;
use App\Services\PdvTotalService;
use Carbon\Carbon;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class VendaCaixaController extends Controller
{
    private const TIPO_PAGAMENTO_CREDIARIO = '06';
    private const TIPO_PAGAMENTO_PIX_QRCODE = '19';

    public function store(
        Request $request,
        LimiteCreditoClienteService $creditoService,
        PdvTotalService $totalService
    )
    {
        $empresaId = (int) $request->empresa_id;
        $lock = null;
        $lockAdquirido = false;

        try {
            $request->replace($this->normalizarUtf8($request->all()));

            $request->validate([
                'empresa_id' => 'required|integer|exists:empresas,id',
                'pdv_token' => 'required|string|max:100',
                'tipo_pagamento' => 'required_without:tipo_pagamento_row|string|nullable',
                'tipo_pagamento_row' => 'required_without:tipo_pagamento|array|nullable',
                'produto_id' => 'required|array|min:1',
                'produto_id.*' => 'required|integer',
                'quantidade' => 'required|array|min:1',
                'quantidade.*' => 'required',
                'valor_unitario' => 'required|array|min:1',
                'valor_unitario.*' => 'required',
                'pix_payment_id' => 'nullable|string|max:64',
                'pix_valor' => 'nullable|numeric|min:0.01',
                'desconto_tipo' => 'nullable|in:fixo,percentual',
                'acrescimo_tipo' => 'nullable|in:fixo,percentual',
                'desconto_valor' => 'nullable',
                'acrescimo_valor' => 'nullable',
                'taxa_entrega' => 'nullable',
            ]);

            $empresaId = (int) $request->empresa_id;
            $token = sha1((string) $request->pdv_token);
            $resultadoKey = "pdv:venda:{$empresaId}:{$token}:resultado";
            $lock = Cache::lock("pdv:venda:{$empresaId}:{$token}:lock", 20);

            if ($vendaId = Cache::get($resultadoKey)) {
                return $this->respostaVenda(VendaCaixa::findOrFail($vendaId));
            }

            if (!$lock->get()) {
                return response()->json([
                    'message' => 'Esta venda j¨¢ est¨¢ sendo processada. Aguarde a confirma0Š40Š0o.',
                    'code' => 'pdv_em_processamento',
                ], 409);
            }

            $lockAdquirido = true;

            if ($vendaId = Cache::get($resultadoKey)) {
                return $this->respostaVenda(VendaCaixa::findOrFail($vendaId));
            }

            $empresa = Empresa::with(['configNota.natureza'])->findOrFail($empresaId);

            if (!$empresa->configNota || !$empresa->configNota->natureza) {
                $this->bloquearVenda(
                    'A configura0Š40Š0o fiscal padr0Š0o da empresa est¨¢ incompleta.',
                    ['empresa_id' => ['Configure a natureza de opera0Š40Š0o padr0Š0o antes de vender.']]
                );
            }

            $vendaCaixa = DB::transaction(function () use (
                $request,
                $creditoService,
                $totalService,
                $empresa
            ) {
                $valorItens = $this->somaItens($request, (int) $empresa->id);
                $ajustes = $totalService->calcular(
                    $valorItens,
                    $request->all(),
                    (float) ($empresa->configNota->percentual_max_desconto ?? 0)
                );
                $desconto = $ajustes['desconto'];
                $acrescimo = $ajustes['acrescimo'];
                $valorTotal = $ajustes['valor_total'];

                $this->validarPagamentos($request, $valorTotal);
                $creditoService->validarVendaPdv($request, $valorTotal);
                $categoriaReceber = $this->categoriaReceber($request, (int) $empresa->id);

                $prevenda = VendaCaixaPreVenda::find($request->prevenda_id);
                if ($prevenda) {
                    $prevenda->status = 1;
                    $prevenda->save();
                }

                $filialId = (string) $request->filial_id === '-1'
                    ? null
                    : $request->filial_id;

                $request->merge([
                    'usuario_id' => $request->usuario_id,
                    'observacao' => $request->observacao ?? '',
                    'qtd_volumes' => $request->qtd_volumes ?? 0,
                    'peso_liquido' => $request->peso_liquido ?? 0,
                    'peso_bruto' => $request->peso_bruto ?? 0,
                    'desconto' => $desconto,
                    'desconto_tipo' => $ajustes['desconto_tipo'],
                    'desconto_percentual' => $ajustes['desconto_percentual'],
                    'valor_total' => $valorTotal,
                    'estado_emissao' => 'novo',
                    'sequencia_cce' => $request->sequencia_cce ?? 0,
                    'chave' => $request->chave ?? 0,
                    'acrescimo' => $acrescimo,
                    'acrescimo_tipo' => $ajustes['acrescimo_tipo'],
                    'acrescimo_percentual' => $ajustes['acrescimo_percentual'],
                    'taxa_entrega' => $ajustes['taxa_entrega'],
                    'natureza_id' => $empresa->configNota->nat_op_padrao,
                    'dinheiro_recebido' => $request->valor_recebido
                        ? $this->moedaParaFloat($request->valor_recebido)
                        : 0,
                    'troco' => 0,
                    'forma_pagamento' => '',
                    'descricao_pag_outros' =>
                        (string) $request->tipo_pagamento === self::TIPO_PAGAMENTO_PIX_QRCODE
                            ? 'PIX Mercado Pago #' . $request->pix_payment_id
                            : ($request->descricao_pag_outros ?? ''),
                    'tipo_pagamento' => $request->tipo_pagamento_row
                        ? '99'
                        : (string) $request->tipo_pagamento,
                    'estado' => 'novo',
                    'nome' => $request->nome,
                    'cpf' => $request->cpf_cnpj ?? '',
                    'pedido_delivery_id' => 0,
                    'qr_code_base64' => 0,
                    'cnpj_cartao' => $request->cnpj_cartao ?? '',
                    'bandeira_cartao' => $request->bandeira_cartao ?? '',
                    'cAut_cartao' => $request->cAut_cartao ?? '',
                    'filial_id' => $filialId,
                ]);

                $vendaCaixa = VendaCaixa::create($request->all());
                $stockMove = new StockMove();

                foreach ($request->produto_id as $indice => $produtoId) {
                    $produto = Produto::query()
                        ->where('id', (int) $produtoId)
                        ->where('empresa_id', (int) $empresa->id)
                        ->firstOrFail();
                    $quantidade = $this->moedaParaFloat($request->quantidade[$indice]);
                    $valorUnitario = $this->moedaParaFloat($request->valor_unitario[$indice]);
                    $cfop = $empresa->configNota->natureza->sobrescreve_cfop
                        ? $empresa->configNota->natureza->CFOP_saida_estadual
                        : $produto->CFOP_saida_estadual;

                    ItemVendaCaixa::create([
                        'venda_caixa_id' => $vendaCaixa->id,
                        'produto_id' => $produto->id,
                        'quantidade' => $quantidade,
                        'valor' => $valorUnitario,
                        'valor_custo' => $produto->valor_compra,
                        'cfop' => $cfop,
                        'observacao' => $request->observacao ?? '',
                        'item_pedido_id' => null,
                    ]);

                    $stockMove->downStock($produto->id, $quantidade, $filialId);
                }

                $this->registrarComissao($request, $vendaCaixa);
                $this->registrarContasReceber(
                    $request,
                    $vendaCaixa,
                    $categoriaReceber,
                    $valorTotal
                );
                $this->registrarFaturasMultiplas($request, $vendaCaixa);
                $this->finalizarComanda($request);
                $this->finalizarOrdemServico($request, $vendaCaixa, $valorTotal);

                return $vendaCaixa;
            });

            Cache::put($resultadoKey, $vendaCaixa->id, now()->addDay());

            if ((string) $request->tipo_pagamento === self::TIPO_PAGAMENTO_PIX_QRCODE) {
                Cache::forget(
                    "pdv:pix:confirmado:{$empresaId}:{$request->pix_payment_id}"
                );
            }

            try {
                EnviarMensagemWhatsAppVenda::dispatch($vendaCaixa, $empresa);
            } catch (\Throwable $e) {
                // Falha de notificacao nunca pode reprovar uma venda confirmada.
                Log::warning('PDV WhatsApp queue dispatch failed.', [
                    'sale_id' => (int) $vendaCaixa->id,
                    'exception' => get_class($e),
                ]);
            }

            return $this->respostaVenda($vendaCaixa);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Existem dados inv¨¢lidos ou incompletos na venda.',
                'errors' => $this->normalizarUtf8($e->errors()),
                'code' => 'validacao_pdv',
            ], 422, [], JSON_INVALID_UTF8_SUBSTITUTE | JSON_UNESCAPED_UNICODE);
        } catch (HttpResponseException $e) {
            throw $e;
        } catch (\Throwable $e) {
            $erroReferencia = substr(sha1(
                get_class($e) . '|' . $e->getFile() . '|' . $e->getLine() . '|' . microtime(true)
            ), 0, 10);

            // Nao reutiliza a mensagem da excecao: ela pode ser justamente o texto invalido.
            Log::error('PDV checkout failed.', [
                'reference' => $erroReferencia,
                'company_id' => $empresaId,
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return response()->json([
                'message' => 'Nao foi possivel finalizar a venda. Codigo: ' . $erroReferencia,
                'code' => 'erro_interno_pdv',
                'error_reference' => $erroReferencia,
            ], 500);
        } finally {
            if ($lock && $lockAdquirido) {
                $lock->release();
            }
        }
    }

    /**
     * Serializa a venda sem transformar bytes legados inv¨¢lidos em erro de checkout.
     * A venda j¨¢ foi confirmada neste ponto; a resposta nunca deve faz¨º-la parecer recusada.
     */
    private function respostaVenda(VendaCaixa $vendaCaixa)
    {
        return response()->json([
            'id' => (int) $vendaCaixa->id,
            'is_os' => (bool) $vendaCaixa->getAttribute('is_os'),
            'id_os' => $vendaCaixa->getAttribute('id_os'),
            'message' => 'Venda finalizada com sucesso.',
        ], 200, [], JSON_UNESCAPED_UNICODE);
    }

    private function normalizarUtf8($valor)
    {
        if (is_array($valor)) {
            foreach ($valor as $chave => $item) {
                $valor[$chave] = $this->normalizarUtf8($item);
            }

            return $valor;
        }

        if (!is_string($valor) || preg_match('//u', $valor) === 1) {
            return $valor;
        }

        $convertido = @iconv('Windows-1252', 'UTF-8//IGNORE', $valor);

        return $convertido === false ? '' : $convertido;
    }

    private function somaItens(Request $request, int $empresaId): float
    {
        $produtos = Arr::wrap($request->produto_id);
        $quantidades = Arr::wrap($request->quantidade);
        $valores = Arr::wrap($request->valor_unitario);

        if (
            count($produtos) !== count($quantidades) ||
            count($produtos) !== count($valores)
        ) {
            $this->bloquearVenda(
                'Os dados dos produtos est0Š0o incompletos.',
                ['produto_id' => ['Revise os itens da venda.']]
            );
        }

        $total = 0.0;

        foreach ($produtos as $indice => $produtoId) {
            $produtoExiste = Produto::query()
                ->where('id', (int) $produtoId)
                ->where('empresa_id', $empresaId)
                ->exists();
            $quantidade = $this->moedaParaFloat($quantidades[$indice]);
            $valorUnitario = $this->moedaParaFloat($valores[$indice]);

            if (!$produtoExiste || $quantidade <= 0 || $valorUnitario < 0) {
                $this->bloquearVenda(
                    'Um dos produtos possui dados inv¨¢lidos.',
                    ["produto_id.{$indice}" => ['Produto, quantidade ou valor inv¨¢lido.']]
                );
            }

            $total += round($quantidade * $valorUnitario, 2);
        }

        return round($total, 2);
    }

    private function validarPagamentos(Request $request, float $valorTotal): void
    {
        $tipos = Arr::wrap($request->tipo_pagamento_row);
        $valores = Arr::wrap($request->valor_integral_row);
        $vencimentos = Arr::wrap($request->data_vencimento_row);

        if ($tipos !== []) {
            if (count($tipos) !== count($valores)) {
                $this->bloquearVenda(
                    'As linhas de pagamento est0Š0o incompletas.',
                    ['tipo_pagamento_row' => ['Revise os pagamentos adicionados.']]
                );
            }

            $totalPagamentos = 0.0;

            foreach ($tipos as $indice => $tipo) {
                $tipo = (string) $tipo;
                $valor = $this->moedaParaFloat($valores[$indice] ?? 0);

                if (!array_key_exists($tipo, VendaCaixa::tiposPagamento()) || $valor <= 0) {
                    $this->bloquearVenda(
                        'Existe uma forma de pagamento inv¨¢lida.',
                        ["tipo_pagamento_row.{$indice}" => ['Tipo ou valor inv¨¢lido.']]
                    );
                }

                if ($tipo === self::TIPO_PAGAMENTO_PIX_QRCODE) {
                    $this->bloquearVenda(
                        'O PIX QR Code deve ser usado como pagamento unico.',
                        ["tipo_pagamento_row.{$indice}" => ['Selecione PIX direto para pagamento dividido.']]
                    );
                }

                if (
                    $tipo === self::TIPO_PAGAMENTO_CREDIARIO &&
                    empty($vencimentos[$indice])
                ) {
                    $this->bloquearVenda(
                        'Informe o vencimento de todas as parcelas do credi¨¢rio.',
                        ["data_vencimento_row.{$indice}" => ['Vencimento obrigat¨®rio.']]
                    );
                }

                $totalPagamentos += $valor;
            }

            if (abs(round($totalPagamentos, 2) - $valorTotal) > 0.01) {
                $this->bloquearVenda(
                    'A soma dos pagamentos deve ser igual ao total da venda.',
                    ['valor_integral_row' => [
                        'Total da venda: R$ ' . number_format($valorTotal, 2, ',', '.') .
                        '. Pagamentos: R$ ' . number_format($totalPagamentos, 2, ',', '.') . '.',
                    ]]
                );
            }

            return;
        }

        $tipo = (string) $request->tipo_pagamento;

        if ($tipo === '' || !array_key_exists($tipo, VendaCaixa::tiposPagamento())) {
            $this->bloquearVenda(
                'Selecione uma forma de pagamento v¨¢lida.',
                ['tipo_pagamento' => ['Forma de pagamento obrigat¨®ria.']]
            );
        }

        if (
            $tipo === '01' &&
            $this->moedaParaFloat($request->valor_recebido) + 0.009 < $valorTotal
        ) {
            $this->bloquearVenda(
                'O valor recebido ¨¦ menor que o total da venda.',
                ['valor_recebido' => ['Informe um valor suficiente.']]
            );
        }

        if ($tipo === self::TIPO_PAGAMENTO_PIX_QRCODE) {
            $pagamentoId = preg_replace('/\D/', '', (string) $request->pix_payment_id);
            $valorPix = $this->moedaParaFloat($request->pix_valor);
            $confirmacao = $pagamentoId !== ''
                ? Cache::get("pdv:pix:confirmado:{$request->empresa_id}:{$pagamentoId}")
                : null;
            $valorConfirmado = (float) data_get($confirmacao, 'valor', 0);

            if (
                $pagamentoId === '' ||
                abs($valorPix - $valorTotal) > 0.01 ||
                !$confirmacao ||
                abs($valorConfirmado - $valorTotal) > 0.01
            ) {
                $this->bloquearVenda(
                    'Confirme o pagamento PIX QR Code antes de finalizar.',
                    ['pix_payment_id' => ['Pagamento PIX pendente ou com valor diferente da venda.']]
                );
            }

            $request->merge(['pix_payment_id' => $pagamentoId]);
        }

        if (
            $tipo === self::TIPO_PAGAMENTO_CREDIARIO &&
            empty($request->data_vencimento)
        ) {
            $this->bloquearVenda(
                'Informe o primeiro vencimento do credi¨¢rio.',
                ['data_vencimento' => ['Vencimento obrigat¨®rio.']]
            );
        }
    }

    private function categoriaReceber(Request $request, int $empresaId): ?CategoriaConta
    {
        $tipos = Arr::wrap($request->tipo_pagamento_row);
        $possuiCrediario =
            (string) $request->tipo_pagamento ===
                self::TIPO_PAGAMENTO_CREDIARIO ||
            in_array(
                self::TIPO_PAGAMENTO_CREDIARIO,
                array_map('strval', $tipos),
                true
            );

        if (!$possuiCrediario) {
            return null;
        }

        $categoria = CategoriaConta::query()
            ->where('empresa_id', $empresaId)
            ->where('tipo', 'receber')
            ->first();

        if (!$categoria) {
            $this->bloquearVenda(
                'Cadastre uma categoria de contas a receber antes de usar o credi¨¢rio.',
                ['categoria_id' => ['Categoria de contas a receber n0Š0o encontrada.']]
            );
        }

        return $categoria;
    }

    private function registrarComissao(Request $request, VendaCaixa $vendaCaixa): void
    {
        if (!$request->vendedor_id) {
            return;
        }

        $vendedor = Funcionario::query()
            ->where('id', (int) $request->vendedor_id)
            ->where('empresa_id', (int) $request->empresa_id)
            ->first();

        if (!$vendedor) {
            $this->bloquearVenda(
                'O vendedor selecionado n0Š0o pertence a esta empresa.',
                ['vendedor_id' => ['Vendedor inv¨¢lido.']]
            );
        }

        ComissaoVenda::create([
            'funcionario_id' => $vendedor->id,
            'venda_id' => $vendaCaixa->id,
            'tabela' => 'venda_caixas',
            'valor' => $this->calcularComissaoVenda(
                $vendaCaixa,
                (float) ($vendedor->percentual_comissao ?? 0)
            ),
            'status' => 0,
            'empresa_id' => $request->empresa_id,
        ]);
    }

    private function registrarContasReceber(
        Request $request,
        VendaCaixa $vendaCaixa,
        ?CategoriaConta $categoria,
        float $valorTotal
    ): void {
        if (
            (string) $request->tipo_pagamento ===
            self::TIPO_PAGAMENTO_CREDIARIO
        ) {
            $quantidade = min(60, max(1, (int) ($request->qd_parcelas ?? 1)));
            $valorBase = floor(($valorTotal / $quantidade) * 100) / 100;
            $resto = $valorTotal - ($valorBase * $quantidade);
            $vencimentoInicial = Carbon::parse($request->data_vencimento);

            for ($indice = 0; $indice < $quantidade; $indice++) {
                $valor = $indice === $quantidade - 1
                    ? round($valorBase + $resto, 2)
                    : $valorBase;

                $this->criarContaReceber(
                    $request,
                    $vendaCaixa,
                    $categoria,
                    $vencimentoInicial->copy()->addMonthsNoOverflow($indice),
                    $valor,
                    'Parcela ' . ($indice + 1) . " de {$quantidade}"
                );
            }
        }

        foreach (Arr::wrap($request->tipo_pagamento_row) as $indice => $tipo) {
            if (
                (string) $tipo !==
                self::TIPO_PAGAMENTO_CREDIARIO
            ) {
                continue;
            }

            $this->criarContaReceber(
                $request,
                $vendaCaixa,
                $categoria,
                Carbon::parse($request->data_vencimento_row[$indice]),
                $this->moedaParaFloat($request->valor_integral_row[$indice]),
                'Linha de credi¨¢rio ' . ($indice + 1),
                $request->obs_row[$indice] ?? ''
            );
        }
    }

    private function criarContaReceber(
        Request $request,
        VendaCaixa $vendaCaixa,
        ?CategoriaConta $categoria,
        Carbon $vencimento,
        float $valor,
        string $referencia,
        string $observacao = ''
    ): void {
        ContaReceber::create([
            'venda_caixa_id' => $vendaCaixa->id,
            'cliente_id' => $request->cliente_id,
            'data_vencimento' => $vencimento,
            'data_recebimento' => $vencimento,
            'valor_integral' => $valor,
            'valor_recebido' => 0,
            'status' => 0,
            'referencia' => $referencia . ' da compra c¨®digo ' . $vendaCaixa->id,
            'categoria_id' => optional($categoria)->id,
            'empresa_id' => $request->empresa_id,
            'juros' => 0,
            'multa' => 0,
            'observacao' => $observacao ?: ($request->obs ?? ''),
            'tipo_pagamento' => self::TIPO_PAGAMENTO_CREDIARIO,
        ]);
    }

    private function registrarFaturasMultiplas(Request $request, VendaCaixa $vendaCaixa): void
    {
        foreach (Arr::wrap($request->tipo_pagamento_row) as $indice => $tipo) {
            FaturaFrenteCaixa::create([
                'valor' => $this->moedaParaFloat($request->valor_integral_row[$indice]),
                'forma_pagamento' => (string) $tipo,
                'venda_caixa_id' => $vendaCaixa->id,
            ]);
        }
    }

    private function finalizarComanda(Request $request): void
    {
        if ((int) $request->codigo_comanda === 0) {
            return;
        }

        $pedido = Pedido::where('comanda', $request->codigo_comanda)->first();
        if ($pedido) {
            $pedido->desativado = true;
            $pedido->save();
        }
    }

    private function finalizarOrdemServico(
        Request $request,
        VendaCaixa $vendaCaixa,
        float $valorTotal
    ): void {
        if (empty($request->is_os)) {
            return;
        }

        $ordemId = Arr::wrap($request->is_os)[0] ?? null;
        $ordem = OrdemServico::find($ordemId);

        if (!$ordem) {
            return;
        }

        $tipo = (string) $request->tipo_pagamento;
        $forma = in_array($tipo, ['17', '19'], true)
            ? 'pix'
            : ($tipo === '01'
                ? 'dinheiro'
                : (in_array($tipo, ['03', '04'], true) ? 'cartao' : null));

        $ordem->estado = 'finalizado';
        $ordem->status_pagamento = 1;
        $ordem->forma_pagamento = $forma;
        $ordem->valor_pago = $valorTotal;
        $ordem->save();

        $vendaCaixa->setAttribute('is_os', true);
        $vendaCaixa->setAttribute('id_os', $ordemId);
    }

    private function calcularComissaoVenda(
        VendaCaixa $vendaCaixa,
        float $percentualComissao
    ): float {
        $valor = 0.0;

        foreach ($vendaCaixa->itens as $item) {
            $percentual = $item->produto->perc_comissao > 0
                ? $item->produto->perc_comissao
                : $percentualComissao;
            $valor += (($item->valor * $item->quantidade) * $percentual) / 100;
        }

        return round($valor, 2);
    }

    private function moedaParaFloat($valor): float
    {
        if (is_int($valor) || is_float($valor)) {
            return (float) $valor;
        }

        $valor = preg_replace('/[^0-9,.-]/', '', trim((string) $valor));

        if ($valor === '') {
            return 0.0;
        }

        if (str_contains($valor, ',')) {
            $valor = str_replace('.', '', $valor);
            $valor = str_replace(',', '.', $valor);
        }

        return is_numeric($valor) ? (float) $valor : 0.0;
    }

    private function bloquearVenda(string $mensagem, array $erros = []): void
    {
        throw new HttpResponseException(response()->json([
            'message' => $mensagem,
            'errors' => $erros,
        ], 422));
    }
}