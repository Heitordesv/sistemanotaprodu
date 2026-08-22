<div class="modal fade" id="modal-lista_pre_venda" aria-modal="true" role="dialog" style="overflow:scroll;"
    tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Pré vendas Recebidas</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body row">
                <div class="table-responsive">
                    <table class="table mb-0 table-striped">
                        <thead>
                            <tr>
                                <th>Vendedor</th>
                                <th>Valor</th>
                                <th>Data</th>
                                <th>Observação</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($preVendas as $item)
                                <tr>
                                    <td>{{ $item->vendedor->nome }}</td>
                                    <td>{{ __moeda($item->valor_total) }}</td>
                                    <td>{{ __data_pt($item->created_at) }}</td>
                                    <td>{{ $item->observacao }}</td>
                                    <td>
                                        
                                        <form method="get" action="{{ route('frenteCaixa.index') }}">
                                            <input type="hidden" value="{{ $item->id }}" name="prevenda_id">
                                            <button class="btn btn-dark">Setar</button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button data-bs-dismiss="modal" type="button" class="btn btn-primary px-5">OK</button>
            </div>
        </div>
    </div>
</div>
