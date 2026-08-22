<div class="card-body p-4">
    <div class="col">
        <h6 class=" text-uppercase"><i class="fab fa-icons-alt"></i></h6>
    </div>
    <h5>Dados da compra</h5>
    {!! __view_locais_select() !!}
    <hr>
    <div class="row">
        <div class="col-md-4 row">
            <button type="button" class="btn btn-outline-primary btn-itens active px-6" onclick="selectDiv2('itens')">1.
                ITENS</button>
        </div>
        <div class="col-md-4 row m-auto">
            <button type="button" class="btn btn-outline-primary btn-frete" onclick="selectDiv2('frete')">2.
                FRETE</button>
        </div>
        <div class="col-md-4 row m-auto">
            <button type="button" class="btn btn-outline-primary btn-pagamento" onclick="selectDiv2('pagamento')">3.
                PAGAMENTO</button>
        </div>
    </div>



    <div class="div-itens row mt-4">
        <h5 class="mt-4">Selecione o fornecedor</h5>
        <div class="col-md-6 mt-1">

            @isset($item)
            {!! __view_locais_select_edit("Local", $item->filial_id) !!}
            @else
            {!! __view_locais_select() !!}
            @endisset
            <label for="inp-fornecedor_id" class="">Fornecedor</label>
            <div class="input-group">
                <select class="form-control select2 fornecedor_id" name="fornecedor_id" id="inp-fornecedor_id">
                    @isset($item)
                    <option value="{{ $item->fornecedor_id }}">{{ $item->fornecedor->razao_social }} - {{ $item->fornecedor->cpf_cnpj }}</option>
                    @endif
                </select>
                <button class="btn btn-primary" type="button" data-bs-toggle="modal" data-bs-target="#modal-fornecedor">
                    <i class="bx bx-plus"></i></button>
            </div>
        </div>
        <h6 class="mt-5">Produtos da compra</h6>
        <div class="col-md-4 mt-3">
            <label for="inp-produto_id" class="">Produto</label>
            <div class="input-group">
                <select class="form-control" name="produto_id" id="inp-produto_id">
                </select>
                <button class="btn btn-primary" type="button" data-bs-toggle="modal" data-bs-target="#modal-produto">
                    <i class="bx bx-plus"></i></button>
            </div>
        </div>
        <div class="col-md-2 mt-3">
            {!! Form::tel('quantidade', 'Quantidade')->attrs(['class' => 'qtd']) !!}
        </div>

        <div class="col-md-2 mt-3">
            {!! Form::tel('valor_unitario', 'Valor unitário de compra')->attrs(['class' => 'moeda value_unit']) !!}
        </div>

        <div class="col-md-2 mt-3">
            {!! Form::tel('subtotal', 'Sub total')->attrs(['class' => 'moeda']) !!}
        </div>

        <div action="" class="col-md-2 mt-3">
            <br>
            <button type="button" class="btn btn-success btn-add-item">Adicionar</button>
        </div>

        <div class="table-responsive">
            <table class="table mb-0 table-striped mt-2 table-itens">
                <thead>
                    <tr>
                        <th>Código do produto</th>
                        <th>Nome</th>
                        <th>Valor unitário</th>
                        <th>Quantidade</th>
                        <th>Subtotal</th>
                        <th>Ações</th>
                    </tr>

                </thead>
                <tbody>
                    @isset($item)

                    @foreach ($item->itens as $product)
                    <tr>
                        <td>
                            <input readonly type="tel" name="produto_id[]" class="form-control" value="{{ $product->produto_id }}">
                        </td>
                        <td>
                            <input readonly type="text" name="produto_nome[]" class="form-control" value="{{ $product->produto->nome }}">
                        </td>
                        <td>
                            <input readonly type="tel" name="valor_unitario[]" class="form-control" value="{{ __moeda($product->valor_unitario) }}">
                        </td>
                        <td>
                            <input readonly type="tel" name="quantidade[]" class="form-control qtd-item" value="{{ $product->quantidade }}">
                        </td>
                        <td>
                            <input readonly type="tel" name="subtotal_item[]" class="form-control subtotal-item" value="{{ __moeda($product->valor_unitario * $product->quantidade) }}">
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
        </div>
    </div>
    <div class="row div-frete d-none  mt-4">
        <h5 class="mt-4">Transportadora</h5>
        <div class="col-md-6 mt-2">
            {!! Form::select(
            'transportadora_id',
            'Transportadora',
            ['' => 'Selecione a transportadora'] + $transportadoras->pluck('razao_social', 'id')->all(),
            )->attrs([
            'class' => 'select2',
            ]) !!}
        </div>
        <h6 class="mt-5">Frete</h6>
        <div class="col-md-6 mt-1">
            {!! Form::select('tipo', 'Tipo', [
            0 => 'Emitente',
            1 => 'Destinatário',
            2 => 'Terceiros',
            3 => 'Sem Frete',
            ])->attrs(['class' => 'select2']) !!}
        </div>
        <div class="col-md-2 mt-1">
            {!! Form::tel('placa', 'Placa')->attrs(['class' => 'placa']) !!}
        </div>
        <div class="col-md-2 mt-1">
            {!! Form::select('uf', 'Uf', App\Models\Veiculo::cUF())->attrs(['class' => 'select2']) !!}
        </div>
        <div class="col-md-2 mt-1">
            {!! Form::tel('valor_frete', 'Valor')->attrs(['class' => 'moeda'])->value(isset($item) ? __moeda($item->valor_frete) : '') !!}
        </div>
        <h6 class="mt-5">Volume</h6>
        <div class="col-md-3 mt-1">
            {!! Form::text('especie', 'Espécie')->attrs(['class' => '']) !!}
        </div>
        <div class="col-md-2 mt-1">
            {!! Form::text('numeracao_volumes', 'N. de volumes')->attrs(['class' => '']) !!}
        </div>
        <div class="col-md-2 mt-1">
            {!! Form::text('qtd_volumes', 'Qtd. volumes')->attrs(['class' => '']) !!}
        </div>
        <div class="col-md-2 mt-1">
            {!! Form::text('peso_liquido', 'Peso líquido')->attrs(['class' => '']) !!}
        </div>
        <div class="col-md-2 mt-1">
            {!! Form::text('peso_bruto', 'Peso bruto')->attrs(['class' => '']) !!}
        </div>
    </div>
    <div class="row div-pagamento d-none mt-4">
        <h5 class="mt-4">Selecione a forma de pagamento</h5>
        <div class="col-md-4 mt-3">
            {!! Form::select('forma_pagamento', 'Forma de pagamento', [
            '' => 'Selecione a forma de pagamento',
            'a_vista' => 'A vista',
            '30_dias' => '30 Dias',
            'personalizado' => 'Personalizado',
            ])->attrs(['class' => 'form-select']) !!}
        </div>
        <div class="col-md-2 mt-3">
            {!! Form::tel('qtd_parcelas', 'Qtd de parcelas')->attrs(['class' => '', 'data-mask' => '00']) !!}
        </div>
        <div class="col-md-2 mt-3">
            {!! Form::date('vencimento_parcela', 'Data vencimento')->attrs(['class' => '']) !!}
        </div>
        <div class="col-md-2 mt-3">
            {!! Form::text('valor_parcela', 'Valor da parcela')->attrs(['class' => 'moeda']) !!}
        </div>
        <div class="col-md-2 mt-3">
            <br>
            <button type="button" class="btn btn-success btn-add-payment">Adicionar</button>
        </div>

        <div class="table-responsive">
            <table class="table mb-0 table-striped mt-2 table-payment">
                <thead>
                    <tr>
                        <th>Vencimento</th>
                        <th>Valor</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>

                    @isset($item)
                    @if(sizeof($item->fatura) > 0)
                    @foreach ($item->fatura as $fatura)
                    <tr>
                        <td>
                            <input readonly type="date" name="vencimento_parcela[]" class="form-control" value="{{ $fatura->data_vencimento }}">
                        </td>
                        <td>
                            <input readonly type="text" name="valor_parcela[]" class="form-control valor-parcela" value="{{ __moeda($fatura->valor_integral) }}">
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
                            <input readonly type="date" name="vencimento_parcela[]" class="form-control" value="{{ \Carbon\Carbon::parse($item->created_at)->format('Y-m-d') }}">
                        </td>
                        <td>
                            <input readonly type="text" name="valor_parcela[]" class="form-control valor-parcela" value="{{ __moeda($item->total) }}">
                        </td>

                        <td>
                            <button class="btn btn-sm btn-danger btn-delete-row">
                                <i class="bx bx-trash"></i>
                            </button>
                        </td>
                    </tr>
                    @endif
                    @endif
                </tbody>
                <tfoot>
                    <tr>
                        <td>Soma pagamento</td>
                        @isset($item)
                        <td class="sum-payment">R$ {{ __moeda($item->total) }}</td>
                        @else
                        <td class="sum-payment">R$ 0,00</td>
                        @endif
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
    <hr>
    <div class="row mt-4">
        <h5>Valor total da compra
            @isset($item)
            <strong class="total-compra">R$ {{ __moeda($item->total) }}</strong>
            @else
            <strong class="total-compra">R$ 0,00</strong>
            @endif
        </h5>
        <div class="col-md-2 mt-1">
            {!! Form::tel('desconto', 'Desconto')->attrs(['class' => 'moeda'])->value(isset($item) ? __moeda($item->desconto) : '') !!}
        </div>
        <div class="col-md-8 mt-1">
            {!! Form::text('observacao', 'Observação')->attrs(['class' => '']) !!}
        </div>
    </div>

    <div class="col-12 alerts mt-4">
    </div>

    <div class="col-12 mt-2">
        <button type="submit" disabled class="btn btn-primary btn-finalizar px-5 w-25">
            @isset($item) Atualizar compra
            @else
            Finalizar compra @endif
        </button>
    </div>
    <br>
</div>
