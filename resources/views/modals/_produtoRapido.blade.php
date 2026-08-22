<div class="modal fade" id="modal-produtoRapido" aria-modal="true" role="dialog" style="overflow:scroll;" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Novo Produto</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3 m-auto">
                    <input type="hidden" value="{{csrf_token()}}" id="token">
                    <input type="hidden" class="divisoes ignore" id="divisoes" value="{{json_encode($divisoes)}}" name="">
                    <input type="hidden" class="subDivisoes ignore" id="subDivisoes" value="{{json_encode($subDivisoes)}}" name="">
                    <div class="div-identificacao row mt-4">
                        <div class="col-md-6">
                            {!! Form::text('nome', 'Descrição')->required() !!}
                        </div>
                        <div class="col-md-2">
                            {!! Form::tel('valor_compra', 'Valor do Custo')->attrs(['class' => 'moeda'])->required()
                            ->value(isset($item) ? __moeda($item->valor_compra) : '')
                            !!}
                        </div>
                        <div class="col-md-2">
                            {!! Form::tel('percentual_lucro', '% de Lucro')
                            ->value(isset($item) ? __moeda($item->percentual_lucro) : '')
                            ->required()
                            ->attrs(['class' => 'perc']) !!}
                        </div>
                        <div class="col-md-2">
                            {!!
                            Form::tel('valor_venda', 'Valor de Venda')->attrs(['class' => 'moeda'])
                            ->value(isset($item) ? __moeda($item->valor_venda) : '')
                            !!}
                        </div>
                        {{-- <div class="col-md-3 mt-2">
                            <div class="form-group">
                                <label for="inp-grade" class="">Tipo Grade</label>
                                <div class="input-group">
                                    <select class="form-control" name="grade" id="inp-grade">
                                        <option value="0">Não</option>
                                        <option value="1">Sim</option>
                                    </select>
                                </div>
                            </div>
                        </div> --}}
                        <div class="col-md-4 mt-2">
                            {!! Form::select('categoria_id', 'Categoria', [null => 'Selecione'] + $categorias->pluck('nome', 'id')->all())
                            ->attrs(['class' => 'form-select']) !!}
                        </div>
                        <div class="col-md-2 mt-2">
                            {!! Form::tel('NCM', 'Ncm')->attrs(['class' => 'ncm'])
                            ->value(isset($item) ? $item->NCM : $tributacao->ncm_padrao) !!}
                        </div>
                        <div class="col-md-2 mt-2">
                            {!! Form::tel('CEST', 'Cest')->attrs(['class' => 'ignore']) !!}
                        </div>
                    </div>
                    <div class="col-12 mt-5">
                        @isset($not_submit)
                        <button type="button" class="btn btn-primary px-5" id="btn-store-produtored">Salvar</button>
                        @else
                        <button type="submit" class="btn btn-primary px-5">Salvar</button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@include('modals._grade2')
