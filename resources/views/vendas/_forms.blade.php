<div class="card-body p-4">
    <div class="col">
        <h6 class="text-uppercase"><i class="fab fa-icons-alt"></i></h6>
    </div>

    <input type="hidden" value="{{$config->parcelamento_maximo}}" id="parcelamento_maximo">

    <div class="row">
        @if(!isset($item))
        @if(!empresaComFilial())
        <div class="row">
            <h6 class="mt-3 col-md-6">Ultimo número NFe:
                <strong class="text-primary">{{ $config->ultimo_numero_nfe }}</strong>
            </h6>
            <h6 class="mt-3 col-md-6">Ambiente:
                <strong>{{ $config->ambiente == 2 ? 'Homologação' : 'Produção' }}</strong>
            </h6>
        </div>
        @endif
        @endif
        <div class="row">

            @isset($item)
            {!! __view_locais_select_edit("Local", $item->filial_id) !!}
            @else
            {!! __view_locais_select() !!}
            @endisset

            <div class="col-md-4 mt-3">
                {!! Form::select('natureza_id', 'Natureza operação', ['' => 'Selecione'] + $naturezas->pluck('info', 'id')->all())->attrs([
                'class' => 'form-select select2',
                ])->required() !!}
            </div>
            <div class="col-md-3 mt-3">
                {!! Form::select(
                'lista_preco',
                'Lista de preço',
                [null => 'Selecione'] + $listaPreco->pluck('nome', 'id')->all(),
                )->attrs(['class' => 'form-select']) !!}
            </div>
        </div>
        <div class="row">
            @isset($item)

            <div class="col-md-6 mt-3">
                <label for="inp-cliente_id" class="required">Cliente</label>
                <div class="input-group">
                    <select class="form-control select2 cliente_id" name="cliente_id" id="inp-cliente_id">
                        @isset($item)
                        <option value="{{ $item->cliente_id }}">{{ $item->cliente->razao_social }} - {{ $item->cliente->cpf_cnpj }}</option>
                        @endif
                    </select>
                    <button class="btn btn-primary" type="button" data-bs-toggle="modal" data-bs-target="#modal-cliente">
                        <i class="bx bx-plus"></i>x
                    </button>
                </div>
            </div>
            @else
            <div class="col-md-6 mt-3">
                <label for="inp-cliente_id" class="">Cliente</label>
                <div class="input-group">
                    <select class="form-control select2 cliente_id" name="cliente_id" id="inp-cliente_id">
                    </select>
                    <button class="btn btn-primary" type="button" data-bs-toggle="modal" data-bs-target="#modal-cliente">
                        <i class="bx bx-plus"></i>
                    </button>
                </div>
            </div>
            @endisset
        </div>
    </div>
    <hr>


    <div class="row m-2">
        <div class="col-md-4 row m-auto">
            <button type="button" class="btn btn-outline-primary btn-itens active " onclick="selectDiv2('itens')">ITENS</button>
        </div>
        <div class="col-md-4 row m-auto">
            <button type="button" class="btn btn-outline-primary btn-transporte" onclick="selectDiv2('transporte')">
            TRANSPORTE</button>
        </div>
        <div class="col-md-4 row m-auto">
            <button type="button" class="btn btn-outline-primary btn-pagamento" onclick="selectDiv2('pagamento')">
            PAGAMENTO</button>
        </div>

        <div class="div-itens row mt-5">
            <div class="col-md-5">
                <div class="form-group">
                    <label for="inp-produto_id" class="">Produto</label>
                    <div class="input-group">
                        <select class="form-control select2" name="produto_id" id="inp-produto_id">
                        </select>
                        <button class="btn btn-primary" type="button" data-bs-toggle="modal" data-bs-target="#modal-produto">
                            <i class="bx bx-plus"></i>
                        </button>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                {!! Form::tel('quantidade', 'Quantidade')->attrs(['class' => 'qtd']) !!}
            </div>
            <div class="col-md-2">
                {!! Form::tel('valor_unitario', 'Valor unitário')->attrs(['class' => 'moeda value_unit']) !!}
            </div>
            <div class="col-md-2">
                {!! Form::tel('subtotal', 'Subtotal')->attrs(['class' => 'moeda']) !!}
            </div>
            <div class="col-md-1">
                <br>
                <button class="btn btn-primary btn-add-item" type="button">
                    <i class="bx bx-plus"></i>
                </button>
            </div>
            <div class="table-responsive">
                <table class="table mb-0 table-striped mt-2 table-itens">
                    <thead>
                        <tr>
                            <th>CÓDIGO</th>
                            <th>NOME</th>
                            <th>VALOR UNITÁRIO</th>
                            <th>QUANTIDADE</th>
                            <th>SUBTOTAL</th>
                            <th>AÇÕES</th>
                        </tr>
                    </thead>
                    <tbody>
                        @isset($item)
                        @foreach($item->itens as $product)
                        <tr>
                            <td>
                                <input readonly type="tel" name="produto_id[]" class="form-control" value="{{ $product->produto_id }}">
                            </td>
                            <td>
                                <input readonly type="text" name="produto_nome[]" class="form-control" value="{{ $product->produto->nome }}">
                            </td>
                            <td>
                                <input readonly type="tel" name="valor_unitario[]" class="form-control" value="{{ __moeda($product->valor) }}">
                            </td>
                            <td>
                                <input readonly type="tel" name="quantidade[]" class="form-control qtd-item" value="{{ __estoque($product->quantidade) }}">
                            </td>
                            <td>
                                <input type="hidden" value="{{ $product->x_pedido }}" name="x_pedido[]" id="x_pedido_row" class="x_pedido_row" value="">
                                <input type="hidden" value="{{ $product->num_item_pedido }}" name="num_item_pedido[]" id="num_item_pedido_row" class="num_item_pedido_row" value="">
                                <input readonly type="tel" name="subtotal_item[]" class="form-control subtotal-item" value="{{ __moeda($product->valor * $product->quantidade) }}">
                            </td>
                            <td>
                                <button class="btn btn-sm btn-danger btn-delete-row">
                                    <i class="bx bx-trash"></i>
                                </button>
                            </td>
                        </tr>
                        @endforeach
                        @endif
                    </tbody>
                </table>

                <h5 class="mt-3">Total de produtos: <strong class="total_produtos"></strong></h5>
            </div>
            <hr>
        </div>

        <div class="row div-transporte d-none  mt-3">
            <h5 class="mt-4">Transportadora</h5>
            <div class="col-md-6 mt-2">
                <div class="form-group">
                    <label for="inp-transportadora_id" class="">Transportadora</label>
                    <div class="input-group">
                        <select class="form-control select2" name="transportadora_id" id="inp-transportadora_id">
                            <option value="">Selecione a transportadora</option>
                            @foreach ($transportadoras as $transp)
                            <option @isset($item) @if($item->transportadora_id == $transp->id) selected @endif @endisset value="{{$transp->id}}">{{$transp->razao_social}}
                            </option>
                            @endforeach
                        </select>
                        <button class="btn btn-primary" type="button" data-bs-toggle="modal" data-bs-target="#modal-transportadora">
                            <i class="bx bx-plus"></i>
                        </button>
                    </div>
                </div>
            </div>

            <h6 class="mt-3">Frete</h6>

            <div class="col-md-2 mt-1">
                {!! Form::select('tipo_frete', 'Tipo', App\Models\Frete::tipos())->attrs(['class' => 'form-select'])
                ->value(isset($item->frete) ? $item->frete->tipo : '') !!}
            </div>

            <div class="col-md-2 mt-1">
                {!! Form::tel('placa_frete', 'Placa')->attrs(['class' => 'placa'])
                ->value(isset($item->frete) ? $item->frete->placa : '') !!}
            </div>

            <div class="col-md-1 mt-1">
                {!! Form::select('uf_frete', 'UF', App\Models\Cidade::estados())->attrs(['class' => 'form-select select2'])
                ->value(isset($item->frete) ? $item->frete->uf : '') !!}
            </div>

            <div class="col-md-2 mt-1">
                {!! Form::tel('valor_frete', 'Valor')->attrs(['class' => 'moeda'])
                ->value(isset($item->frete) ? __moeda($item->frete->valor) : '') !!}
            </div>

            <h6 class="mt-3">Volume</h6>
            <div class="col-md-3 mt-1">
                {!! Form::text('especie_frete', 'Espécie')->attrs(['class' => ''])
                ->value(isset($item->frete) ? $item->frete->uf : '') !!}
            </div>

            <div class="col-md-2 mt-1">
                {!! Form::text('n_volumes_frete', 'N. de volumes')->attrs(['class' => ''])
                ->value(isset($item->frete) ? $item->frete->numeracaoVolumes : '') !!}
            </div>

            <div class="col-md-2 mt-1">
                {!! Form::text('q_volumes_frete', 'Qtd. volumes')->attrs(['class' => ''])
                ->value(isset($item->frete) ? $item->frete->qtdVolumes : '') !!}
            </div>

            <div class="col-md-2 mt-1">
                {!! Form::text('peso_liquido_frete', 'Peso liquido')->attrs(['class' => 'peso'])
                ->value(isset($item->frete) ? $item->frete->peso_liquido : '') !!}
            </div>

            <div class="col-md-2 mt-1">
                {!! Form::text('peso_bruto_frete', 'Peso bruto')->attrs(['class' => 'peso'])
                ->value(isset($item->frete) ? $item->frete->peso_bruto : '') !!}
            </div>

            <hr style="margin-top: 20px;">
        </div>

        <div class="row div-pagamento d-none mt-3">
            <div class="row col-6">
                <h5 class="mt-4">Selecione a forma de pagamento</h5>
                <div class="col-10 mt-3">
                    {!! Form::select('tipo_pagamento', 'Tipo de pagamento', App\Models\Venda::tiposPagamento())->attrs([
                    'class' => 'select2'])->value(isset($item) ? $item->tipo_pagamento : '') !!}
                </div>

                <div class="col-10 mt-3">
                    {!! Form::select('forma_pagamento', 'Forma de pagamento', [
                    '' => 'Selecione a forma de pagamento',
                    'a_vista' => 'A vista',
                    '30_dias' => '30 Dias',
                    'personalizado' => 'Personalizado',
                    ])->attrs(['class' => 'form-select']) !!}
                </div>

                <div class="col-5 mt-3">
                    {!! Form::text('qtd_parcelas', 'Qtd de parcelas')->attrs(['class' => '', 'data-mask' => '00']) !!}
                </div>

                <div class="col-5 mt-3 data_vencimento">
                    {!! Form::date('data_vencimento', 'Data vencimento')->attrs(['class' => '']) !!}
                </div>

                <div class="col-5 mt-3">
                    {!! Form::tel('valor_integral', 'Valor da parcela')->attrs(['class' => 'moeda']) !!}
                </div>

                <div class="col-3 mt-3">
                    <br>
                    <button type="button" class="btn btn-success btn-add-payment">Adicionar</button>
                </div>
                <div class="col-3 mt-3">
                    <br>
                    <button type="button" onclick="renderizarPagamento()" id="btn-personalizado" data-bs-toggle="modal" data-bs-target="#modal-pagamento_personalizado" class="btn btn-info disabled"><i class="bx bx-list-ul"></i></button>
                </div>

            </div>
            <div class="col mt-5">
                <div class="table-responsive">
                    <table class="table mb-0 table-striped mt-2 table-payment">
                        <thead>
                            <tr>
                                <th>Tipo de pagamento</th>
                                <th>Vencimento</th>
                                <th>Valor</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            @isset($item)
                            @if(sizeof($item->duplicatas) > 0)
                            @foreach($item->duplicatas as $duplicatas)
                            <tr>
                                <td>
                                    <input readonly type="tel" name="forma_pagamento_parcela[]" class="form-control" value="{{ $duplicatas->getTipoPagamento() }}">
                                </td>
                                <td>
                                    <input type="date" name="data_vencimento[]" class="form-control" value="{{ $duplicatas->data_vencimento }}">
                                </td>
                                <td>
                                    <input readonly type="text" name="valor_parcela[]" class="form-control valor_integral" value="{{ __moeda($duplicatas->valor_integral) }}">
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-danger btn-delete-row">
                                        <i class="bx bx-trash"></i>
                                    </button>
                                </td>
                            </tr>
                            @endforeach
                            @else
                            <tr>
                                <td>
                                    <input readonly type="hidden" name="forma_pagamento" class="form-control" value="{{ $item->forma_pagamento }}">
                                    <input readonly type="tel" name="forma_pagamento_get" class="form-control" value="{{ $item->getTipoPagamento() }}">
                                </td>
                                <td>
                                    <input readonly type="text" name="data_vencimento[]" class="form-control" value="{{ __data_pt($item->data_registro, 0) }}">
                                </td>
                                <td>
                                    <input readonly type="text" name="valor_parcela[]" class="form-control valor_integral" value="{{ __moeda($item->valor_total) }}">
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-danger btn-delete-row">
                                        <i class="bx bx-trash"></i>
                                    </button>
                                </td>
                            </tr>
                            @endif
                            @endisset
                        </tbody>
                        <tfoot>
                            <tr>
                                <td>Soma pagamento</td>
                                @isset($item)
                                <td class="sum-payment">R$ {{ __moeda($item->valor_total) }}</td>
                                @else
                                <td class="sum-payment">R$ 0,00</td>
                                @endif
                                <td>
                                    <button class="btn btn-danger btn-sm" type="button" id="remover_parcelas">Remover parcelas</button>
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
            <hr style="margin-top: 20px;">
        </div>

       <div class="row rodape mt-3">
    <div class="row g-3">
        <div class="col-md-3 mt-5">
            <button class="btn btn-warning" type="button" data-bs-toggle="modal" data-bs-target="#modal-ref-nfe">
                <i class="bx bx-list-ol"></i>Referenciar NFe
            </button>
        </div>
        <div class="col-md-2 mt-1">
            <br>
            {!! Form::date('data_entrega', 'Data de entrega')->attrs(['class' => '']) !!}
        </div>
        <div class="col-md-2 mt-1">
            <br>
            {!! Form::tel('desconto', 'Desconto')->attrs(['class' => 'moeda desconto'])->value(isset($item) ?
            __moeda($item->desconto) : '') !!}
        </div>
        <div class="col-md-2 mt-1">
            <br>
            {!! Form::tel('acrescimo', 'Acréscimo')->attrs(['class' => 'moeda acrescimo'])->value(isset($item) ?
            __moeda($item->acrescimo) : '') !!}
        </div>
        
        {{-- CAMPO DE OBSERVAÇÃO AUMENTADO --}}
        <div class="col-md-12 mt-1">
            <br>
            {!! Form::textarea('observacao', 'Observações / Informações Adicionais')->attrs(['class' => 'form-control', 'rows' => 3]) !!}
        </div>
        
        <div class="row mt-4">
            <h5>
                Valor Total:
                @isset($item)
                <strong class="total-venda">R$ {{ __moeda($item->valor_total) }}</strong>
                @else
                <strong class="total-venda">R$ 0,00</strong>
                @endif
            </h5>
        </div>
    </div>
</div>
            <div class="col-12 alerts mt-4">
            </div>
        </div>
        <input type="hidden" value="" id="type" name="type">
        <div class="row mt-4">
            <div class="col-md-6">
                <button onclick="salvar('orcamento')" type="button" disabled class="btn btn-warning btn-venda px-5 w-100">
                    Salvar como orçamento
                </button>
            </div>
            <div class="col-md-6">
                <button onclick="salvar('venda')" type="button" disabled class="btn btn-primary btn-venda px-5 w-100">
                    Salvar venda
                </button>
            </div>
        </div>
    </div>
</div>
