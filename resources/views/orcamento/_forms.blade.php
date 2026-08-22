<div class="row g-3">

    <input type="hidden" value="{{$data->id}}" name="orcamento_id">

    <h5 class="mb-0 text-uppercase">Orçamento código: <strong>{{ $data->id }}</strong></h5>

    <div class="mt-3">
        <h6>Cliente: <strong>{{ $data->cliente->razao_social }}</strong></h6>
        <h6>Cnpj: <strong>{{ $data->cliente->cpf_cnpj }}</strong></h6>
        <h6>Data: <strong>{{ __data_pt($data->created_at, 0) }}</strong></h6>
        <h6>Valor Integral: <strong>{{ __moeda($data->valor_total) }}</strong></h6>
        <h6>Cidade: <strong>{{ $data->cliente->cidade->nome }}</strong></h6>
        <h6>Dias restantes para o vencimento: <strong>{{ $diasParaVencimento }}</strong></h6>
        <h6>Estado: @if ($data->estado == 'NOVO')
            <strong class="text-info">NOVO</strong>
            @elseif($data->estado == 'APROVADO')
            <strong class="text-success">APROVADO</strong>
            @else
            <strong class="text-danger">REPROVADO</strong>
            @endif
        </h6>
    </div>

    <div class="row mt-4">
        {{-- <div class="col-md-2">
            {!!Form::date('vencimento', 'Data Vencimento')->value($data->vencimento)
            !!}
        </div> --}}
        <div class="col-md-2">
            <label for="">Data Vencimento</label>
            <input type="datetime" disabled class="form-control" value="{{ __data_pt($data->validade, 0) }}">
        </div>

        <div class="col-md-10">
            {!! Form::text('obs', 'Observação') !!}
        </div>
    </div>
    <hr>

    {!!Form::open()
    ->post()
    ->route('orcamentoVenda.addItem')
    !!}
    <div class="row mt-3">
        <div class="col-md-6">
            <div class="form-group">
                <label for="inp-produto_id" class="">Produto</label>
                <div class="input-group">
                    <select class="form-control produto_id" name="produto_id" id="inp-produto_id">

                    </select>
                    {{-- <button class="btn btn-primary" type="button" data-bs-toggle="modal"
                    data-bs-target="#modal-produto">
                    <i class="bx bx-plus"></i>
                </button> --}}
            </div>
        </div>
    </div>
    <input type="hidden" value="{{$data->id}}" name="orcamento_id" class="orc_id">
    <div class="col-md-1">
        {!! Form::tel('quantidade', 'Quantidade')->attrs(['class' => 'qtd']) !!}
    </div>
    <div class="col-md-2">
        {!! Form::tel('valor_unitario', 'Valor Unitário')->attrs(['class' => 'moeda value_unit']) !!}
    </div>
    <div class="col-md-2">
        {!! Form::tel('subtotal', 'Subtotal')->attrs(['class' => 'moeda']) !!}
    </div>

    <div class="col-md-1 text-left">
        <br>
        @if(!isset($not_submit))
        <button class="btn btn-primary btn-add-item" type="submit"><i class="bx bx-plus"></i></button>
        @endif
    </div>
</div>
{!! Form::close() !!}

<div class="card mt-3">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table mb-0 table-striped table-itens">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Produto</th>
                        <th>Valor</th>
                        <th>Quantidade</th>
                        <th>Subtotal</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @isset($data)

                    @foreach ($data->itens as $key => $product)
                    <tr>
                        <td>
                            <input readonly type="tel" name="produto_id[]" class="form-control" value="{{ $product->produto_id }}">
                        </td>
                        <td>
                            <input readonly type="text" name="produto_nome[]" class="form-control" value="{{ $product->produto->nome }}">
                        </td>
                        <td>
                            <input readonly type="tel" name="valor[]" class="form-control" value="{{ __moeda($product->valor) }}">
                        </td>
                        <td>
                            <input readonly type="tel" name="quantidade[]" class="form-control qtd-item" value="{{ __estoque($product->quantidade) }}">
                        </td>
                        <td>
                            <input readonly type="tel" name="subtotal_item[]" class="form-control subtotal-item" value="{{ __moeda($product->valor * $product->quantidade) }}">
                        </td>
                        <td>
                            <a href="/orcamentoVenda/destroyItem/{{$product->id}}" class="btn btn-sm btn-danger btn-delete-row"><i class="bx bx-trash"></i></a>
                            {{-- <button class="btn btn-sm btn-danger btn-delete-row">
                                <i class="bx bx-trash"></i>
                            </button> --}}
                        </td>
                    </tr>
                    @endforeach

                    @endif

                </tbody>
            </table>
        </div>
    </div>
    <div class="m-2">
        @foreach ($data->itens as $p)

        @endforeach
        <h6>Soma dos Itens: {{__moeda($somaItens)}}</h6>
        <h6>Desconto: {{ __moeda($data->desconto) }}</h6>
        <h6>Acréscimo: {{ __moeda($data->acrescimo) }}</h6>
        <h6 class="total-venda">Valor Total: <strong>{{ __moeda($data->valor_total) }}</strong></h6>

    </div>
</div>
<hr>

<div class="row">

    {!!Form::open()
    ->post()
    ->autocomplete('off')
    ->route('orcamentoVenda.addPagamentos')
    !!}
    <input type="hidden" value="{{$data->id}}" name="orcamento_id">
    <div class="row">
        <div class="col-md-2">
            {!! Form::tel('valor', 'Valor')->value(__moeda($data->valor_total))->attrs(['class' => '']) !!}
        </div>
        <div class="col-md-2">
            {!! Form::date('vencimento', 'Data Vencimento')->attrs(['class' => '']) !!}
        </div>
        <div class="col-md-1 text-left">
            <br>
            @if(!isset($not_submit))
            <button class="btn btn-primary" type="submit"> <i class="bx bx-plus"></i></button>
            @endif
        </div>
        <div class="col-md-1">
            <br>
            <button class="btn btn-primary" type="button" data-bs-toggle="modal" data-bs-target="#modal-pagamentos_orcamento"><i class="bx bx-list-ol"></i></button>
        </div>
    </div>
    {!!Form::close()!!}

    <div class="table-responsive">
        <table class="table mb-0 table-striped">
            <thead class="table">
                <tr>
                    <th>Forma de Pagamento</th>
                    <th>Vencimento</th>
                    <th>Valor</th>
                    <th>Ações</th>

                </tr>
            </thead>
            <tbody>
                @if(sizeof($data->duplicatas) > 0)

                @foreach ($data->duplicatas as $item)
                <tr>
                    <td>{{ $item->getTipoPagamento() }}</td>
                    <td>{{ (isset($item) ? __data_pt($item->vencimento, 0) : $data->created_at) }}</td>
                    <td>{{ __moeda($item->valor) }}</td>
                    <td>
                        <a href="/orcamentoVenda/destroyParcela/{{$item->id}}" class="btn btn-danger">
                            <i class="bx bx-trash"></i>
                        </a>
                    </td>
                </tr>
                @endforeach
                @else
                <td>{{ $data->getTipoPagamento() }}</td>
                <td>{{ __data_pt($data->created_at, 0) }}</td>
                <td>{{ __moeda($data->valor_total) }}</td>
                <td>
                    <a href="/orcamentoVenda/destroyParcela/{{$data->id}}" class="btn btn-danger">
                        <i class="bx bx-trash"></i>
                    </a>
                </td>
                @endif
            </tbody>
        </table>
    </div>
    <div class="row mt-1">
        <div class="col-md-2 mt-3">
            <a type="btn" style="width: 100%" class="btn btn-info px-5" target="_blank" href="{{ route('orcamentoVenda.imprimir', $data->id) }}">
                <i class="bx bx-printer"></i>Imprimir
            </a>
        </div>
        <div class="col-md-2 mt-3">
            <a @if($data->estado != 'NOVO') disabled @endif href="{{ route('orcamentoVenda.reprovar', $data->id) }}" class="btn btn-danger">
                <i class="bx bx-x"></i>
                Alterar para reprovado
            </a>
        </div>
        <hr class="mt-3">
        <div class="row">
            <form method="get" action="{{ route('orcamentoVenda.enviarEmail') }}">
                <input type="hidden" name="id" value="{{$data->id}}">
                <input type="hidden" name="redirect" value="true">
                <div class="row">
                    <div class="col-md-3 mt-3">
                        {!! Form::text('email', 'Email')->attrs(['class' => '']) !!}
                    </div>
                    <div class="col-md-3 mt-3">
                        <br>
                        <button type="submit" class="btn btn-success">
                            <i class="bx bx-envelope"></i>Enviar e-mail
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
</div>
<br>
<hr>
</div>
{!! Form::open()->post()
->route('orcamentoVenda.store')
->multipart() !!}

<input type="hidden" value="{{$data->id}}" name="orcamento_id">

<div class="row mt-3">
    <h6>Frete</h6>
    <div class="col-md-2 mt-3">
        {!! Form::select('tipo', 'Tipo de frete', [
        9 => 'Sem Frete',
        0 => 'Emitente',
        1 => 'Destinatário',
        2 => 'Terceiros',
        
        ])->attrs(['class' => 'select2']) !!}
    </div>
    <div class="col-md-2 mt-3">
        {!! Form::text('placa', 'Placa do veículo')->attrs(['class' => 'placa']) !!}
    </div>
    <div class="col-md-2 mt-3">
        {!! Form::select('uf', 'UF', [null => 'Selecione'] + App\Models\Orcamento::estados())->attrs([
        'class' => 'select2',
        ]) !!}
    </div>
    <div class="col-md-2 mt-3">
        {!! Form::tel('valor', 'Valor do Frete')->attrs(['class' => 'moeda']) !!}
    </div>
    <div class="col-md-4 mt-3">
        {!! Form::select('natureza_id', 'Natureza de Operação', ['' => 'Selecione'] + $naturezaOperacao->pluck('natureza', 'id')->all())->attrs([
        'class' => 'select2',
        ])->required() !!}
    </div>
    <div class="col-md-2 mt-3">
        {!! Form::tel('numeracaoVolumes', 'Numeração de Volumes')->attrs(['class' => '']) !!}
    </div>
    <div class="col-md-2 mt-3">
        {!! Form::tel('qtdVolumes', 'Quantidade de Volumes')->attrs(['class' => '']) !!}
    </div>
    <div class="col-md-2 mt-3">
        {!! Form::tel('peso_liquido', 'Peso Liquido')->attrs(['class' => '']) !!}
    </div>
    <div class="col-md-2 mt-3">
        {!! Form::tel('peso_bruto', 'Peso Bruto')->attrs(['class' => '']) !!}
    </div>
    <div class="col-md-2 mt-3">
        {!! Form::text('especie', 'Espécie')->attrs(['class' => '']) !!}
    </div>

    <div class="col-md-12 mt-4">

        <button @if($data->estado != 'NOVO') disabled @endif type="submit" class="btn btn-success px-5">
            <i class="bx bx-check"></i>
            Gerar Venda
        </button>

    </div>
</div>
{!!Form::close()!!}
