<div class="modal fade" id="modal-fluxo_diario" aria-modal="true" role="dialog" style="overflow:scroll;" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Fluxo Diário</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            @if ($abertura != null)

            <div class="modal-body row">
                <div class="col-12">
                    <h6>Abertura de Caixa: <strong class="" style="color: blue">{{ __moeda($abertura->valor) }}</strong></h6>
                </div>
                <hr>
                <div class="col-12">
                    @foreach ($sangrias as $item)
                    <h6>Sangria: R$<strong style="color: red">{{ __moeda($item->valor) }}</strong> - {{__data_pt($item->created_at)}}</h6>
                    @endforeach
                </div>
                <hr>
                <div class="col-12">
                    @foreach ($suprimentos as $item)
                    <h6>Suprimentos: <strong style="color: green">{{ isset($item) ? $item->valor : '0,00' }}</strong></h6>
                    @endforeach
                </div>
                <hr>
                <div class="table-responsive">
                    <h4>Vendas</h4>
                    <table class="table mb-0 table-striped">
                        <thead>
                            <tr>
                                <th>Hórario</th>
                                <th>Valor</th>
                                <th>Tipo de Pagamento</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($vendas as $item)
                            <tr>
                                <td>{{ $item->created_at }}</td>
                                <td>{{ __moeda($item->valor_total) }}</td>
                                <td>{{ $item->getTipoPagamento($item->tipo_pagamento) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button data-bs-dismiss="modal" type="button" class="btn btn-primary px-5">OK</button>
            </div>
            @else
            <div>
                <div class="modal-body">
                    <h5>Abrir caixa</h5>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
