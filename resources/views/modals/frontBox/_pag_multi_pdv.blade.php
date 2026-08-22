<div class="modal fade" id="modal-pag_multi_pdv" aria-modal="true" role="dialog" style="overflow:scroll;" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title mb-1" style="color:blue">
                        Pagamento Múltiplo R$:
                        <strong class="total-venda-modal">@isset($item){{ __moeda($item->valor_total) }}@endif</strong>
                    </h5>
                    <small class="text-muted">Distribua o valor entre diferentes formas de pagamento.</small>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>

            <div class="modal-body">
                <input type="hidden" id="venda_desconto_modal" value="@isset($item){{ $item->desconto }}@else 0 @endisset">

                <div class="row g-3 align-items-end">
                    <div class="col-md-3">
                        {!! Form::select(
                            'tipo_pagamento_row',
                            'Tipo de Pagamento',
                            ['' => 'Selecione'] + App\Models\Venda::tiposPagamento()
                        )->attrs(['class' => 'form-select']) !!}
                    </div>

                    <div class="col-md-2">
                        {!! Form::tel('valor_integral_row', 'Valor')->attrs(['class' => 'moeda']) !!}
                        <small id="ajuda-valor-crediario" class="text-muted d-none">
                            Informe o valor total que será parcelado no crediário.
                        </small>
                    </div>

                    <div class="col-md-2">
                        {!! Form::date('data_vencimento_row', 'Primeiro vencimento')
                            ->attrs(['class' => ''])
                            ->value(date('Y-m-d')) !!}
                    </div>

                    <div class="col-md-4">
                        {!! Form::text('obs_row', 'Observação')->attrs(['class' => '']) !!}
                    </div>

                    <div class="col-md-1">
                        <button type="button" class="btn btn-info btn-add-payment w-100" title="Adicionar pagamento">
                            <i class="bx bx-plus"></i>
                        </button>
                    </div>
                </div>

                <div id="configuracao-parcelas-crediario" class="card border-primary mt-3 d-none">
                    <div class="card-body py-3">
                        <div class="row g-3 align-items-end">
                            <div class="col-md-3">
                                <label for="quantidade_parcelas_row" class="form-label fw-bold">Quantidade de parcelas</label>
                                <input
                                    type="number"
                                    id="quantidade_parcelas_row"
                                    class="form-control"
                                    min="1"
                                    max="60"
                                    value="1"
                                    inputmode="numeric"
                                >
                            </div>

                            <div class="col-md-3">
                                <label for="intervalo_parcelas_row" class="form-label fw-bold">Intervalo</label>
                                <select id="intervalo_parcelas_row" class="form-select">
                                    <option value="1" selected>Mensal</option>
                                    <option value="2">A cada 2 meses</option>
                                    <option value="3">A cada 3 meses</option>
                                    <option value="6">Semestral</option>
                                    <option value="12">Anual</option>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <div id="resumo-parcelamento-crediario" class="alert alert-info mb-0 py-2">
                                    Informe o valor e a quantidade para calcular as parcelas.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="table-responsive mt-3">
                    <table class="table mb-0 table-striped table-payment align-middle">
                        <thead>
                            <tr>
                                <th>Tipo de Pagamento</th>
                                <th>Vencimento</th>
                                <th>Valor</th>
                                <th>Observações</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if(isset($item) && $item != null && $item->fatura)
                                @foreach ($item->fatura as $fatura)
                                    <tr>
                                        <td>
                                            <input readonly type="text" name="tipo_pagamento_row[]" class="form-control" value="{{ $fatura->forma_pagamento }}">
                                        </td>
                                        <td>
                                            <input readonly type="date" name="data_vencimento_row[]" class="form-control" value="{{ $fatura->vencimento }}">
                                        </td>
                                        <td>
                                            <input readonly type="text" name="valor_integral_row[]" class="form-control valor_integral" value="{{ __moeda($fatura->valor) }}">
                                        </td>
                                        <td>
                                            <input readonly type="text" name="obs_row[]" class="form-control" value="{{ $fatura->obs_row }}">
                                        </td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-danger btn-delete-row" title="Remover parcela">
                                                <i class="bx bx-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            @endif
                        </tbody>
                        <tfoot>
                            <tr>
                                <td class="fw-bold">Soma dos pagamentos</td>
                                <td colspan="4" class="sum-payment fw-bold">
                                    @isset($item)
                                        R$ {{ __moeda($item->valor_total) }}
                                    @else
                                        R$ 0,00
                                    @endif
                                </td>
                            </tr>
                        </tfoot>
                    </table>

                    <div class="mt-3">
                        <h6 class="text-danger mt-2">
                            Diferença: <strong class="sum-restante"></strong>
                        </h6>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button data-bs-dismiss="modal" id="btn-pag_row" type="button" disabled class="btn btn-primary">
                    Confirmar pagamentos
                </button>
            </div>
        </div>
    </div>
</div>

<script src="/js/frontBoxParcelas.js?v=1"></script>