<div class="card-body p-4">
    <div class="col">
        <h6 class="text-uppercase"><i class="fab fa-icons-alt"></i></h6>
    </div>
    <h5>Dados da NFe</h5>
    <hr>
    <input type="hidden" value="{{$config->parcelamento_maximo}}" id="parcelamento_maximo">

    <div class="row">
        @if(!isset($item))
        <div class="row">
            <h6 class="mt-3 col-md-6">Ultimo número NFe:
                <strong class="text-primary">{{ $config->ultimo_numero_nfe }}</strong>
            </h6>
        </div>
        @endif
        <div class="row">
            @if(!empresaComFilial())
            <div class="row">

                <div class="col-lg-4 col-md-4 col-sm-6">
                    @if($config->ambiente == 2)
                    <h6>Ambiente: <strong class="text-primary">Homologação</strong></h6>
                    @else
                    <h6>Ambiente: <strong class="text-success">Produção</strong></h6>
                    @endif
                </div>
            </div>
            @endif
            {!! __view_locais_select() !!}
            <div class="col-md-3 mt-3">
                {!! Form::select('natureza_id', 'Natureza operação', $naturezas->pluck('info', 'id'))->attrs([
                'class' => 'form-select select2',
                ]) !!}
            </div>
            <div class="col-md-3 mt-3">
                {!! Form::select(
                'lista_preco',
                'Lista de preço',
                [null => 'Selecione'] + $listaPreco->pluck('nome', 'id')->all(),
                )->attrs(['class' => 'form-select']) !!}
            </div>
            <div class="col-md-2 mt-3">
                {!! Form::select('baixa_estoque','Baixar estoque', [1 => 'Sim', 0 => 'Não'])
                ->attrs(['class' => 'form-select']) !!}
            </div>
            <div class="col-md-2 mt-3">
                {!! Form::select('gerar_conta_receber','Gerar conta a receber', [0 => 'Não', 1 => 'Sim'])
                ->attrs(['class' => 'form-select']) !!}
            </div>
            <div class="col-md-2 mt-3">
                {!! Form::select('tipo_nfe','Tipo NFe', ['normal' => 'Normal', 'remessa' => 'Remessa', 'estorno' => 'Estorno', 'entrada' => 'Entrada'])
                ->attrs(['class' => 'form-select']) !!}
            </div>
        </div>
        @isset($item)
        <div class="col-md-8 mt-3">
            <label for="inp-cliente_id" class="">Cliente</label>
            <div class="input-group">
                <select class="form-control select2 cliente_id" name="cliente_id" id="inp-cliente_id">
                    @isset($item)
                    <option value="{{ $item->cliente_id }}">{{ $item->cliente->razao_social }} - {{ $item->cliente->cpf_cnpj }}</option>
                    @endif
                </select>
                <button class="btn btn-primary" type="button" data-bs-toggle="modal" data-bs-target="#modal-cliente">
                    <i class="bx bx-plus"></i>
                </button>
            </div>
        </div>
        @else
        <div class="col-md-8 mt-3">
            <label for="inp-cliente_id" class="">Cliente</label>
            <div class="input-group">
                <select class="form-control select2 cliente_id" name="cliente_id" id="inp-cliente_id">
                </select>
                <button class="btn btn-primary" type="button" data-bs-toggle="modal" data-bs-target="#modal-cliente">
                    <i class="bx bx-plus"></i></button>
            </div>
        </div>
        @endisset
    </div>
    <hr>
    <div class="row mt-4">
        <div class="col-md-4 row">
            <button type="button" class="btn btn-outline-primary btn-itens active px-6" onclick="selectDiv2('itens')">ITENS</button>
        </div>
        <div class="col-md-4 row m-auto">
            <button type="button" class="btn btn-outline-primary btn-transporte" onclick="selectDiv2('transporte')">
                TRANSPORTE</button>
        </div>
        <div class="col-md-4 row m-auto">
            <button type="button" class="btn btn-outline-primary btn-pagamento" onclick="selectDiv2('pagamento')">
                PAGAMENTO</button>
        </div>
    </div>

    <div class="div-itens row mt-5">
        <div class="tab-pane" id="produtos" role="tabpanel">
            <div class="card">
                <div class="row m-3">
                    <div class="table-responsive">
                        <table class="table table-dynamic table-produtos" style="width: 4800px">
                            <thead>
                                <tr>
                                    <th>Produto</th>
                                    <th>Qtd</th>
                                    <th>Valor Unt</th>
                                    <th>Subtotal</th>
                                    <th>%ICMS</th>
                                    <th>VALOR ICMS</th>
                                    <th>%PIS</th>
                                    <th>VALOR PIS</th>
                                    <th>%COFINS</th>
                                    <th>VALOR COFINS</th>
                                    <th>%IPI</th>
                                    <th>VALOR IPI</th>
                                    <th>%RED BC</th>
                                    <th>CFOP</th>
                                    <th>NCM</th>
                                    <th>CEST</th>
                                    <th>MODBCST</th>
                                    <th>R$ VBC ICMS</th>
                                    <th>R$ VBC PIS</th>
                                    <th>R$ VBC COFINS</th>
                                    <th>R$ VBC IPI</th>
                                    <th>VBCSTRET</th>
                                    <th>VFRETE</th>
                                    <th>VBCST</th>
                                    <th>PICMSST</th>
                                    <th>VICMSST</th>
                                    <th>PMVAST</th>
                                    <th>DESC. PEDIDO</th>
                                    <th>Nº ITEM PEDIDO</th>
                                    <th>CST CSOSN</th>
                                    <th>CST PIS</th>
                                    <th>CST COFINS</th>
                                    <th>CST IPI</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                @isset($item)
                                @foreach ($item->itens as $prod)
                                <tr class="dynamic-form">
                                    <td width="250">
                                        <select class="form-control select2 produto_id" name="produto_id[]" id="">
                                            <option value="">Selecione..</option>
                                            @foreach ($produtos as $p)
                                            <option @if($prod->produto_id == $p->id) selected @endif value="{{$p->id}}">{{$p->nome}}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td width="80">
                                        <input value="{{ __moeda($prod->quantidade) }}" class="form-control qtd" type="tel" name="quantidade[]" id="inp-quantidade">
                                    </td>
                                    <td width="100">
                                        <input value="{{ __moeda($prod->valor_unitario) }}" class="form-control moeda valor_unit" type="tel" name="valor_unitario[]" id="inp-valor_unitario">
                                    </td>
                                    <td width="120">
                                        <input value="{{ __moeda($prod->sub_total) }}" class="form-control moeda sub_total" type="tel" name="sub_total[]" id="inp-subtotal">
                                    </td>
                                    <td width="80">
                                        <input value="{{ $prod->perc_icms }}" class="form-control" type="tel" name="perc_icms[]" id="inp-perc_icms">
                                    </td>
                                    <td width="80">
                                        <input value="{{ $prod->valor_icms }}" class="form-control" type="tel" name="valor_icms[]" id="inp-valor_icms">
                                    </td>
                                    <td width="80">
                                        <input value="{{ $prod->perc_pis }}" class="form-control" type="tel" name="perc_pis[]" id="inp-perc_pis">
                                    </td>
                                    <td width="80">
                                        <input value="{{ $prod->valor_pis }}" class="form-control" type="tel" name="valor_pis[]" id="inp-valor_pis">
                                    </td>
                                    <td width="80">
                                        <input value="{{ $prod->perc_cofins }}" class="form-control" type="tel" name="perc_cofins[]" id="inp-perc_cofins">
                                    </td>
                                    <td width="80">
                                        <input value="{{ $prod->valor_cofins }}" class="form-control" type="tel" name="valor_cofins[]" id="inp-valor_cofins">
                                    </td>
                                    <td width="80">
                                        <input value="{{ $prod->perc_ipi }}" class="form-control" type="tel" name="perc_ipi[]" id="inp-perc_ipi">
                                    </td>
                                    <td width="80">
                                        <input value="{{ $prod->valor_ipi }}" class="form-control" type="tel" name="valor_ipi[]" id="inp-valor_ipi">
                                    </td>
                                    <td width="80">
                                        <input value="{{ number_format($prod->pRedBC, 2) }}" class="form-control perc ignore" type="tel" name="perc_red_bc[]" id="inp-perc_red_bc">
                                    </td>
                                    <td width="80">
                                        <input value="{{ $prod->cfop }}" class="form-control cfop ignore" type="tel" name="cfop[]" id="inp-cfop_estadual">
                                    </td>
                                    <td width="100">
                                        <input value="{{ $prod->ncm }}" class="form-control ignore" type="tel" name="ncm[]" id="inp-ncm">
                                    </td>
                                    <td width="100">
                                        <input value="{{ $prod->cest }}" class="form-control ignore" type="tel" name="cest[]" id="inp-cest">
                                    </td>
                                    <td width="100">
                                        <select class="form-control select2" name="modBCST[]">
                                            @foreach(App\Models\Produto::modalidadesDeterminacaoST() as $key => $c)
                                            <option @if($prod->modBCST == $key) selected @endif value="{{$key}}">{{$c}}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td width="100">
                                        <input value="{{ $prod->vbc_icms }}" class="form-control ignore moeda" type="tel" name="vbc_icms[]" id="inp-vbc_icms[]">
                                    </td>
                                    <td width="100">
                                        <input value="{{ $prod->vbc_pis }}" class="form-control ignore moeda" type="tel" name="vbc_pis[]" id="inp-vbc_pis[]">
                                    </td>
                                    <td width="100">
                                        <input value="{{ $prod->vbc_cofins }}" class="form-control ignore moeda" type="tel" name="vbc_cofins[]" id="inp-vbc_cofins[]">
                                    </td>
                                    <td width="100">
                                        <input value="{{ $prod->vbc_ipi }}" class="form-control ignore moeda" type="tel" name="vbc_ipi[]" id="inp-vbc_ipi[]">
                                    </td>
                                    <td width="100">
                                        <input value="{{ $prod->vBCSTRet }}" class="form-control ignore moeda" type="tel" name="vBCSTRet[]" id="inp-vBCSTRet">
                                    </td>
                                    <td width="100">
                                        <input value="{{ $prod->vFrete }}" class="form-control ignore moeda" type="tel" name="vFrete[]" id="inp-vFrete">
                                    </td>
                                    <td width="100">
                                        <input value="{{ $prod->vBCST }}" class="form-control ignore moeda" type="tel" name="vBCST[]" id="inp-vBCST[]">
                                    </td>
                                    <td width="100">
                                        <input value="{{ $prod->pICMSST }}" class="form-control ignore moeda" type="tel" name="pICMSST[]" id="inp-pICMSST[]">
                                    </td>
                                    <td width="100">
                                        <input value="{{ $prod->vICMSST }}" class="form-control ignore moeda" type="tel" name="vICMSST[]" id="inp-vICMSST[]">
                                    </td>
                                    <td width="100">
                                        <input value="{{ $prod->pMVAST }}" class="form-control ignore moeda" type="tel" name="pMVAST[]" id="inp-pMVAST[]">
                                    </td>
                                    <td width="100">
                                        <input value="{{ $prod->x_pedido }}" class="form-control ignore" type="tel" name="x_pedido[]" id="inp-x_pedido[]">
                                    </td>
                                    <td width="100">
                                        <input value="{{ $prod->num_item_pedido }}" class="form-control ignore" type="tel" name="num_item_pedido[]" id="inp-num_item_pedido[]">
                                    </td>
                                    <td width="250">
                                        <select name="cst_csosn[]" class="form-control select2">
                                            @foreach(App\Models\Produto::listaCSTCSOSN() as $key => $c)
                                            <option @if($prod->cst_csosn == $key) selected @endif value="{{$key}}">{{$c}}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td width="250">
                                        <select name="cst_pis[]" class="form-control select2">
                                            @foreach(App\Models\Produto::listaCST_PIS_COFINS() as $key => $c)
                                            <option @if($prod->cst_pis == $key) selected @endif value="{{$key}}">{{$c}}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td width="250">
                                        <select name="cst_cofins[]" class="form-control select2">
                                            @foreach(App\Models\Produto::listaCST_PIS_COFINS() as $key => $c)
                                            <option @if($prod->cst_cofins == $key) selected @endif value="{{$key}}">{{$c}}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td width="250">
                                        <select name="cst_ipi[]" class="form-control select2">
                                            @foreach(App\Models\Produto::listaCST_IPI() as $key => $c)
                                            <option @if($prod->cst_ipi == $key) selected @endif value="{{$key}}">{{$c}}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td width="30">
                                        <button class="btn btn-danger btn-remove-tr">
                                            <i class="bx bx-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                                @endforeach
                                @else
                                <tr class="dynamic-form">
                                    <td width="250">
                                        <select required class="form-control select2 produto_id" name="produto_id[]" id="">
                                            <option value="">Selecione..</option>
                                            @foreach ($produtos as $p)
                                            <option value="{{$p->id}}">{{$p->nome}}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td width="80">
                                        <input class="form-control qtd" type="tel" name="quantidade[]" id="inp-quantidade">
                                    </td>
                                    <td width="100">
                                        <input class="form-control moeda valor_unit" type="tel" name="valor_unitario[]" id="inp-valor_unitario">
                                    </td>
                                    <td width="120">
                                        <input class="form-control moeda sub_total" type="tel" name="sub_total[]" id="inp-subtotal">
                                    </td>
                                    <td width="80">
                                        <input class="form-control" type="tel" name="perc_icms[]" id="inp-perc_icms">
                                    </td>
                                    <td width="80">
                                        <input class="form-control" type="tel" name="valor_icms[]" id="inp-valor_icms">
                                    </td>
                                    <td width="80">
                                        <input class="form-control" type="tel" name="perc_pis[]" id="inp-perc_pis">
                                    </td>
                                    <td width="80">
                                        <input class="form-control" type="tel" name="valor_pis[]" id="inp-valor_pis">
                                    </td>
                                    <td width="80">
                                        <input class="form-control" type="tel" name="perc_cofins[]" id="inp-perc_cofins">
                                    </td>
                                    <td width="80">
                                        <input class="form-control" type="tel" name="valor_cofins[]" id="inp-valor_cofins">
                                    </td>
                                    <td width="80">
                                        <input class="form-control" type="tel" name="perc_ipi[]" id="inp-perc_ipi">
                                    </td>
                                    <td width="80">
                                        <input class="form-control" type="tel" name="valor_ipi[]" id="inp-valor_ipi">
                                    </td>
                                    <td width="80">
                                        <input class="form-control perc ignore" type="tel" name="perc_red_bc[]" id="inp-perc_red_bc">
                                    </td>
                                    <td width="80">
                                        <input class="form-control cfop ignore" type="tel" name="cfop[]" id="inp-cfop_estadual">
                                    </td>
                                    <td width="150">
                                        <input class="form-control ignore" type="tel" name="ncm[]" id="inp-cfop_outro_estado">
                                    </td>
                                    <td width="150">
                                        <input class="form-control ignore" type="tel" name="cest[]" id="inp-cest">
                                    </td>
                                    <td width="150">
                                        <select class="form-control ignore select2" name="modBCST[]">
                                            @foreach(App\Models\Produto::modalidadesDeterminacaoST() as $key => $c)
                                            <option value="{{$key}}">{{$c}}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td width="150">
                                        <input class="form-control ignore moeda" type="tel" name="vbc_icms[]" id="inp-vbc_icms">
                                    </td>
                                    <td width="150">
                                        <input class="form-control ignore moeda" type="tel" name="vbc_pis[]" id="inp-vbc_pis">
                                    </td>
                                    <td width="150">
                                        <input class="form-control ignore moeda" type="tel" name="vbc_cofins[]" id="inp-vbc_cofins">
                                    </td>
                                    <td width="150">
                                        <input class="form-control ignore moeda" type="tel" name="vbc_ipi[]" id="inp-vbc_ipi">
                                    </td>
                                    <td width="150">
                                        <input class="form-control ignore moeda" type="tel" name="vBCSTRet[]" id="inp-vBCSTRet">
                                    </td>
                                    <td width="150">
                                        <input class="form-control ignore moeda" type="tel" name="vFrete[]" id="inp-vFrete">
                                    </td>
                                    <td width="150">
                                        <input class="form-control ignore moeda" type="tel" name="vBCST[]" id="inp-vBCST[]">
                                    </td>
                                    <td width="150">
                                        <input class="form-control ignore moeda" type="tel" name="pICMSST[]" id="inp-pICMSST[]">
                                    </td>
                                    <td width="150">
                                        <input class="form-control ignore moeda" type="tel" name="vICMSST[]" id="inp-vICMSST[]">
                                    </td>
                                    <td width="150">
                                        <input class="form-control ignore moeda" type="tel" name="pMVAST[]" id="inp-pMVAST[]">
                                    </td>
                                    <td width="150">
                                        <input class="form-control ignore" type="tel" name="x_pedido[]" id="inp-x_pedido[]">
                                    </td>
                                    <td width="150">
                                        <input class="form-control ignore" type="tel" name="num_item_pedido[]" id="inp-num_item_pedido[]">
                                    </td>
                                    <td width="250">
                                        <select name="cst_csosn[]" class="form-control select2">
                                            @foreach(App\Models\Produto::listaCSTCSOSN() as $key => $c)
                                            <option value="{{$key}}">{{$c}}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td width="250">
                                        <select name="cst_pis[]" class="form-control select2">
                                            @foreach(App\Models\Produto::listaCST_PIS_COFINS() as $key => $c)
                                            <option value="{{$key}}">{{$c}}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td width="250">
                                        <select name="cst_cofins[]" class="form-control select2">
                                            @foreach(App\Models\Produto::listaCST_PIS_COFINS() as $key => $c)
                                            <option value="{{$key}}">{{$c}}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td width="250">
                                        <select name="cst_ipi[]" class="form-control select2">
                                            @foreach(App\Models\Produto::listaCST_IPI() as $key => $c)
                                            <option value="{{$key}}">{{$c}}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td width="30">
                                        <button class="btn btn-danger btn-remove-tr">
                                            <i class="bx bx-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                                @endisset
                            </tbody>
                        </table>
                    </div>
                    <div class="row col-12 col-lg-3 mt-3">
                        <br>
                        <button type="button" class="btn btn-info btn-add-tr px-3">
                            Adicionar Produto
                        </button>
                    </div>
                    <div class="mt-3">
                        <h5>Total de Produtos: <strong class="total_prod">R$ 0,00</strong></h5>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row div-transporte d-none  mt-3">
        <h5 class="mt-4">Transportadora</h5>
        <div class="col-md-6 mt-2">
            <div class="form-group">
                <label for="inp-transportadora_id" class="">Transportadora</label>
                <div class="input-group">
                    <select class="form-control select2" name="transportadora_id" id="inp-transportadora_id">
                        <option value="">Selecione a transportadora...</option>
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
        <div class="col-md-6 mt-1">
            {!! Form::select('tipo_frete', 'Tipo', [
            0 => 'Emitente',
            1 => 'Destinatário',
            2 => 'Terceiros',
            3 => 'Sem Frete',
            ])->attrs(['class' => 'form-select']) !!}
        </div>

        <div class="col-md-2 mt-1">
            {!! Form::tel('placa', 'Placa')->attrs(['class' => 'placa']) !!}
        </div>

        <div class="col-md-2 mt-1">
            {!! Form::tel('uf', 'UF')->attrs(['class' => 'uf']) !!}
        </div>

        <div class="col-md-2 mt-1">
            {!! Form::tel('valor_frete', 'Valor')->attrs(['class' => 'moeda valor_frete'])->value(isset($item) ? __moeda($item->valor_frete) : '') !!}
        </div>

        <h6 class="mt-3">Volume</h6>
        <div class="col-md-3 mt-1">
            {!! Form::text('especie', 'Espécie')->attrs(['class' => '']) !!}
        </div>

        <div class="col-md-2 mt-1">
            {!! Form::text('n_volumes', 'N. de volumes')->attrs(['class' => '']) !!}
        </div>

        <div class="col-md-2 mt-1">
            {!! Form::text('q_volumes', 'Qtd. volumes')->attrs(['class' => '']) !!}
        </div>

        <div class="col-md-2 mt-1">
            {!! Form::text('peso_liquido', 'Peso liquido')->attrs(['class' => '']) !!}
        </div>

        <div class="col-md-2 mt-1">
            {!! Form::text('peso_bruto', 'Peso bruto')->attrs(['class' => '']) !!}
        </div>

        {{-- <div class="col-md-4">
                <div class="form-group">
                    <label for="inp-calcular_frete" class=""></label>
                    <div class="input-group">
                        <button class="btn btn-info" type="button" data-bs-toggle="modal" data-bs-target="#modal-calcular_frete">
                            <i class="bx bx-train"></i>Calcular frete
                        </button>
                    </div>
                </div>
            </div> --}}
        <br>
    </div>

    <div class="row div-pagamento d-none mt-3">
        <div class="row col-6">
            <h5 class="mt-4">Selecione a forma de pagamento</h5>
            <div class="col-10 mt-3">
                {!! Form::select('tipo_pagamento', 'Tipo de pagamento', App\Models\Venda::tiposPagamento())->attrs([
                'class' => 'select2'])->value(isset($item) ? $item->forma_pagamento : '') !!}
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

            <div class="col-5 mt-3">
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
                            <th>Tipo de Pagamento</th>
                            <th>Vencimento</th>
                            <th>Valor</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if(isset($item))
                        @if(sizeof($item->fatura) > 0)
                        @foreach($item->fatura as $fatura)
                        <tr>
                            <td>
                                <input readonly type="tel" name="forma_pagamento_parcela[]" class="form-control" value="{{ $fatura->getTipo() }}">
                            </td>
                            <td>
                                <input readonly type="date" name="data_vencimento[]" class="form-control" value="{{ $fatura->data_vencimento }}">
                            </td>
                            <td>
                                <input readonly type="text" name="valor_parcela[]" class="form-control valor_integral" value="{{ __moeda($fatura->valor) }}">
                            </td>
                            <td>
                                <button class="btn btn-sm btn-danger btn-delete-row">
                                    <i class="bx bx-trash"></i>
                                </button>
                            </td>
                        </tr>
                        @endforeach

                        @endif
                        @endif
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
    </div>
    <hr>
    <div class="row rodape mt-3">
        <div class="row g-3">
            <div class="col-md-3 mt-5">
                <button class="btn btn-warning" type="button" data-bs-toggle="modal" data-bs-target="#modal-ref-nfe">
                    <i class="bx bx-list-ol"></i>Referenciar NFe
                </button>
            </div>
            <div class="col-md-3 mt-1">
                <br>
                {!! Form::date('data_entrega', 'Data entrega')->attrs(['class' => '']) !!}
            </div>
            <div class="col-md-3 mt-1">
                <br>
                {!! Form::date('data_retroativa', 'Data de emissão retroativa')->attrs(['class' => '']) !!}
            </div>
            <div class="col-md-2 mt-1">
                <br>
                {!! Form::tel('desconto', 'Desconto')->attrs(['class' => 'moeda desconto'])->value(isset($item) ?
                __moeda($item->desconto) : '') !!}
            </div>
            <div class="col-md-2 mt-1">
                <br>
                {!! Form::tel('acrescimo', 'Acréscimo')->attrs(['class' => 'moeda acrescimo'])->value(isset($item) ? __moeda($item->acrescimo) : '') !!}
            </div>
            <div class="col-md-5 mt-1">
                <br>
                {!! Form::tel('informacao', 'Informação adicional')->attrs(['class' => '']) !!}
            </div>
            <div class="row mt-4">
                <h5>
                    Valor Total:
                    <strong class="total-venda">R$ </strong>
                    <input type="hidden" class="total-geral" name="valor_total" value="">
                </h5>
            </div>
        </div>
        <div class="col-12 alerts mt-4">
        </div>
    </div>
    <input type="hidden" value="" id="type" name="type">
    <div class="row mt-4">

        <div class="col-md-3 float-right">
            <button type="submit" disabled class="btn btn-primary btn-venda px-5 w-100">
                Salvar NFe
            </button>
        </div>
    </div>
</div>

@include('modals._ref-nfe', ['not_submit' => true])
