<div class="row g-3">

    <div class="card">

        @isset($pathXml)
        <input type="hidden" name="xmlEntrada" value="{{ $pathXml }}">
        @else
        <input type="hidden" name="xmlEntrada" value="{{ $item->chave_nf_entrada }}">
        @endisset

        @isset($dadosNf)
        <input type="hidden" name="nNf" value="{{ $dadosNf['nNf'] }}">
        <input type="hidden" name="chave_nf_entrada" value="{{ $dadosNf['chave'] }}">

        @else
        <input type="hidden" name="nNf" value="{{ $item->nNf }}">
        @endisset

        <div class="card-body">
            @isset($dadosNf)
            <h5>Chave: <strong class="text-primary">{{ $dadosNf['chave'] }}</strong></h5>
            @else

            @endif
            
            @isset($dadosEmitente)
            <div class="row">
                <div class="col-lg-6">
                    <h6>Fornecedor: <strong>{{$dadosEmitente['razaoSocial']}}</strong></h6>
                    <h6>Nome Fantasia: <strong>{{$dadosEmitente['nomeFantasia']}}</strong></h6>
                    <h6>Logradouro: <strong>{{$dadosEmitente['logradouro']}}</strong></h6>
                    <h6>Numero: <strong>{{$dadosEmitente['numero']}}</strong></h6>
                    <h6>Bairro: <strong>{{$dadosEmitente['bairro']}}</strong></h6>
                </div>
                <div class="col-lg-6">
                    <h6>CNPJ: <strong>{{$dadosEmitente['cnpj']}}</strong></h6>
                    <h6>IE: <strong>{{$dadosEmitente['ie']}}</strong></h6>
                    <h6>CEP: <strong>{{$dadosEmitente['cep']}}</strong></h6>
                    <h6>Fone: <strong>{{$dadosEmitente['fone']}}</strong></h6>
                </div>
                <input type="hidden" name="fornecedor_id" value="{{ $idFornecedor }}">
            </div>
            @else
            <div class="row">
                <div class="col-lg-6">
                    <h6>Fornecedor: <strong>{{$item->fornecedor->razao_social}}</strong></h6>
                    <h6>Nome Fantasia: <strong>{{$item->fornecedor->nome_fantasia}}</strong></h6>
                    <h6>Logradouro: <strong>{{$item->fornecedor->rua}}</strong></h6>
                    <h6>Numero: <strong>{{$item->fornecedor->numero}}</strong></h6>
                    <h6>Bairro: <strong>{{$item->fornecedor->bairro}}</strong></h6>
                </div>
                <div class="col-lg-6">
                    <h6>CNPJ: <strong>{{$item->fornecedor->cpf_cnpj}}</strong></h6>
                    <h6>IE: <strong>{{$item->fornecedor->ie_rg}}</strong></h6>
                    <h6>CEP: <strong>{{$item->fornecedor->cep}}</strong></h6>
                    <h6>Fone: <strong>{{$item->fornecedor->telefone}}</strong></h6>
                </div>
                <input type="hidden" name="fornecedor_id" value="{{ $item->fornecedor->id }}">
            </div>
            @endisset
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h6 class="mt-4">Itens da NFe</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table mb-0 table-striped mt-2 table-itens" style="width: 3500px">
                    <thead>
                        <tr>
                            <th>Código</th>
                            <th style="width: 400px;">Produto</th>
                            <th style="width: 150px;">NCM</th>
                            <th style="width: 100px;">CFOP</th>
                            <th style="width: 150px;">Código de Barra</th>
                            <th style="width: 150px;">Un. Compra</th>
                            <th style="width: 150px;">Valor</th>
                            <th style="width: 150px;">Quantidade</th>
                            <th style="width: 150px;">Subtotal</th>

                            <th style="width: 100px;">Desconto</th>
                            <th style="width: 100px;">%ICMS</th>
                            <th style="width: 100px;">%PIS</th>
                            <th style="width: 100px;">%COFINS</th>
                            <th style="width: 100px;">%IPI</th>
                            <th style="width: 200px;">CSOSN / CST</th>
                            <th style="width: 200px;">CST PIS</th>
                            <th style="width: 200px;">CST COFINS</th>
                            <th style="width: 200px;">CST IPI</th>

                            <th style="width: 240px;">CEST</th>

                            <th style="width: 100px;">% Red Base Calc</th>
                            <th style="width: 100px;">Cód ANP</th>
                            <th style="width: 100px;">Valor Partida</th>
                            <th style="width: 100px;">% Glp</th>
                            <th style="width: 100px;">% Gnn</th>
                            <th style="width: 100px;">% Gni</th>
                        </tr>
                    </thead>
                    <tbody>
                        @isset($itens)
                        @foreach($itens as $it)
                        <tr>
                            <td>
                                <input readonly type="tel" name="codigo[]" class="form-control" value="{{$it['codigo']}}">
                            </td>
                            <td>
                                <input type="text" name="nome[]" class="form-control" value="{{$it['xProd']}}">
                            </td>
                            <td>
                                <input type="tel" name="ncm[]" class="form-control ncm" value="{{$it['ncm']}}">
                            </td>
                            <td>
                                <input type="tel" name="cfop[]" class="form-control cfop" value="{{$it['cfop']}}">
                            </td>
                            <td>
                                <input type="tel" name="codBarras[]" class="form-control" value="{{$it['codBarras']}}">
                            </td>
                            <td>
                                <input readonly type="text" name="unidade_medida[]" class="form-control" value="{{$it['unidade_medida']}}">
                            </td>
                            <td>
                                <input type="tel" name="valor_unit[]" class="form-control valor_unit moeda" value="{{ __moeda($it['vUnCom']) }}">
                            </td>
                            <td>
                                <input type="tel" name="quantidade[]" class="form-control qtd" value="{{ __moeda((float)($it['qCom'])) }}">
                            </td>
                            <td>
                                <input type="tel" name="subtotal[]" class="form-control subtotal-item moeda" value="{{ __moeda($it['vUnCom'] * (float)$it['qCom']) }}">
                            </td>

                            <td>
                                <input type="tel" name="vDesc[]" class="form-control desconto moeda" value="{{ __moeda($it['vDesc']) }}">
                            </td>
                            <td>
                                <input type="tel" name="perc_icms[]" class="form-control perc" value="{{ number_format($it['perc_icms'], 2, '.', '') }}">
                            </td>
                            <td>
                                <input type="tel" name="perc_pis[]" class="form-control perc" value="{{ number_format($it['perc_pis'], 2, '.', '') }}">
                            </td>
                            <td>
                                <input type="tel" name="perc_cofins[]" class="form-control perc" value="{{ number_format($it['perc_cofins'], 2, '.', '') }}">
                            </td>
                            <td>
                                <input type="tel" name="perc_ipi[]" class="form-control perc" value="{{ number_format($it['perc_ipi'], 2, '.', '') }}">
                            </td>
                            <td>
                                <select name="cst_csosn[]" class="form-control select2">
                                    @foreach(App\Models\Produto::listaCSTCSOSN() as $key => $c)
                                    <option @if($it['cst_csosn']==$key) selected @endif value="{{$key}}">{{$c}}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td>
                                <select name="cst_pis[]" class="form-control select2">
                                    @foreach(App\Models\Produto::listaCST_PIS_COFINS() as $key => $c)
                                    <option @if($it['cst_pis']==$key) selected @endif value="{{$key}}">{{$c}}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td>
                                <select name="cst_cofins[]" class="form-control select2">
                                    @foreach(App\Models\Produto::listaCST_PIS_COFINS() as $key => $c)
                                    <option @if($it['cst_cofins']==$key) selected @endif value="{{$key}}">{{$c}}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td>
                                <select name="cst_ipi[]" class="form-control select2">
                                    @foreach(App\Models\Produto::listaCST_IPI() as $key => $c)
                                    <option @if($it['cst_ipi']==$key) selected @endif value="{{$key}}">{{$c}}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td>
                                <input type="tel" name="CEST[]" class="form-control" value="{{$it['CEST']}}">
                            </td>
                            <td>
                                <input type="tel" name="pRedBC[]" class="form-control" value="{{$it['pRedBC']}}">
                            </td>
                            <td>
                                <input type="tel" name="codigo_anp[]" class="form-control" value="{{$it['codigo_anp']}}">
                            </td>
                            <td>
                                <input type="tel" name="valor_partida[]" class="form-control" value="{{$it['valor_partida']}}">
                            </td>
                            <td>
                                <input type="tel" name="perc_glp[]" class="form-control" value="{{$it['perc_glp']}}">
                            </td>
                            <td>
                                <input type="tel" name="perc_gnn[]" class="form-control" value="{{$it['perc_gnn']}}">
                            </td>
                            <td>
                                <input type="tel" name="perc_gni[]" class="form-control" value="{{$it['perc_gni']}}">
                            </td>
                            <td>
                                <button class="btn btn-sm btn-danger btn-delete-row">
                                    <i class="bx bx-trash"></i>
                                </button>
                            </td>
                        </tr>
                        @endforeach

                        @else
                        @foreach($item->itens as $it)
                        <tr>
                            <td>
                                <input readonly type="tel" name="codigo[]" class="form-control" value="{{$it->cod}}">
                            </td>
                            <td>
                                <input type="text" name="nome[]" class="form-control" value="{{$it->nome}}">
                            </td>
                            <td>
                                <input type="tel" name="ncm[]" class="form-control ncm" value="{{$it->ncm}}">
                            </td>
                            <td>
                                <input type="tel" name="cfop[]" class="form-control cfop" value="{{$it->cfop}}">
                            </td>
                            <td>
                                <input type="tel" name="codBarras[]" class="form-control" value="{{$it->codBarras}}">
                            </td>
                            <td>
                                <input readonly type="text" name="unidade_medida[]" class="form-control" value="{{$it->unidade_medida}}">
                            </td>
                            <td>
                                <input type="tel" name="valor_unit[]" class="form-control valor_unit moeda" value="{{ __moeda((float)($it->valor_unit)) }}">
                            </td>
                            <td>
                                <input type="tel" name="quantidade[]" class="form-control qtd" value="{{ __moeda((float)($it->quantidade)) }}">
                            </td>
                            <td>
                                <input type="tel" name="subtotal[]" class="form-control subtotal-item moeda" value="{{ __moeda($it->valor_unit * (float)$it->quantidade) }}">
                            </td>

                            <td>
                                <input type="tel" name="vDesc[]" class="form-control desconto moeda" value="{{ __moeda($it->vDesc) }}">
                            </td>
                            <td>
                                <input type="tel" name="perc_icms[]" class="form-control perc" value="{{$it->perc_icms}}">
                            </td>
                            <td>
                                <input type="tel" name="perc_pis[]" class="form-control perc" value="{{$it->perc_pis}}">
                            </td>
                            <td>
                                <input type="tel" name="perc_cofins[]" class="form-control perc" value="{{$it->perc_cofins}}">
                            </td>
                            <td>
                                <input type="tel" name="perc_ipi[]" class="form-control perc" value="{{$it->perc_ipi}}">
                            </td>
                            <td>
                                <select name="cst_csosn[]" class="form-control select2">
                                    @foreach(App\Models\Produto::listaCSTCSOSN() as $key => $c)
                                    <option @if($it['cst_csosn']==$key) selected @endif value="{{$it->cst_csosn}}">{{$c}}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td>
                                <select name="cst_pis[]" class="form-control select2">
                                    @foreach(App\Models\Produto::listaCST_PIS_COFINS() as $key => $c)
                                    <option @if($it['cst_pis']==$key) selected @endif value="{{$it->cst_pis}}">{{$c}}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td>
                                <select name="cst_cofins[]" class="form-control select2">
                                    @foreach(App\Models\Produto::listaCST_PIS_COFINS() as $key => $c)
                                    <option @if($it['cst_cofins']==$key) selected @endif value="{{$it->cst_cofins}}">{{$c}}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td>
                                <select name="cst_ipi[]" class="form-control select2">
                                    @foreach(App\Models\Produto::listaCST_IPI() as $key => $c)
                                    <option @if($it['cst_ipi']==$key) selected @endif value="{{$it->cst_ipi}}">{{$c}}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td>
                                <input type="tel" name="CEST[]" class="form-control" value="{{$it->CEST}}">
                            </td>
                            <td>
                                <input type="tel" name="pRedBC[]" class="form-control" value="{{$it->pRedBC}}">
                            </td>
                            <td>
                                <input type="tel" name="codigo_anp[]" class="form-control" value="{{$it->codigo_anp}}">
                            </td>
                            <td>
                                <input type="tel" name="valor_partida[]" class="form-control" value="{{$it->valor_partida}}">
                            </td>
                            <td>
                                <input type="tel" name="perc_glp[]" class="form-control" value="{{$it->perc_glp}}">
                            </td>
                            <td>
                                <input type="tel" name="perc_gnn[]" class="form-control" value="{{$it->perc_gnn}}">
                            </td>
                            <td>
                                <input type="tel" name="perc_gni[]" class="form-control" value="{{$it->perc_gni}}">
                            </td>
                            <td>
                                <button class="btn btn-sm btn-danger btn-delete-row">
                                    <i class="bx bx-trash"></i>
                                </button>
                            </td>
                        </tr>
                        @endforeach

                        @endisset
                    </tbody>
                </table>
            </div>
        </div>
        <h5 class="m-3">Soma dos itens: <strong id="soma-itens"></strong></h5>
        <input type="hidden" name="valor_devolucao" id="valor_devolucao" value="">
    </div>
    <div class="row mt-3">
        <!-- <h3>Fatura</h3> -->
        <div class="col-md-6">
            @isset($naturezas)
            {!! Form::select('natureza_id', 'Natureza de Operação', ['' => 'Selecione'] + $naturezas->pluck('natureza', 'id')->all())->attrs([
            'class' => 'select2',
            ])->required()->value() !!}
            @else
            {!! Form::select('natureza_id', 'Natureza de Operação', ['' => 'Selecione'] + $naturezas->pluck('natureza', 'id')->all())->attrs([
            'class' => 'select2',
            ])->required()->value($item->natureza->natureza) !!}
            @endisset
        </div>
        <div class="col-md-2">
            {!! Form::select('tipo', 'Tipo', ['' => 'Selecione'] + [1 => 'Saída', 0 => 'Entrada'])->attrs(['class' => 'form-select'])->required() !!}
        </div>
        <div class="col-md-4">
            @isset($transportadoras)
            {!! Form::select('transportadora_id', 'Transportadora', ['' => 'Selecione'] + $transportadoras->pluck('razao_social', 'id')->all())->attrs(['class' => 'form-select'])->value() !!}
            @else
            {!! Form::select('transportadora_id', 'Transportadora', ['' => 'Selecione'] + $transportadoras->pluck('razao_social', 'id')->all())->attrs(['class' => 'form-select'])->value($item->transportadora->razao_social) !!}
            @endisset
        </div>
        @isset($transp)
        <input type="hidden" name="transportadora_id" value="{{ $idTransportadora }}">
        @endisset
    </div>
    <hr class="mt-4">
    <div class="row">
        <h3>Frete</h3>
        <div class="col-md-2">
            @isset($dadosNf)
            {!! Form::tel('vFrete', 'Valor do frete')->attrs(['class' => 'moeda'])->value($dadosNf['vFrete']) !!}
            @else
            {!! Form::tel('vFrete', 'Valor do frete')->attrs(['class' => 'moeda'])->value(__moeda($item->vFrete)) !!}
            @endisset
        </div>
        @isset($transportadora)
        <div class="col-md-2">
            {!! Form::select('frete_tipo', 'Tipo de frete', ['' => 'Selecione'] + [0 => 'Emitente', 1 => 'Destinatário', 2 => 'Terceiros', 9 => 'Sem frete'])->attrs(['class' => 'form-select'])->value($transportadora['frete_tipo']) !!}
        </div>
        <div class="col-md-2">
            {!! Form::text('veiculo_placa', 'Placa')->attrs(['class' => 'placa'])->value($transportadora['veiculo_placa']) !!}
        </div>
        <div class="col-md-2">
            {!! Form::select('transportadora_uf', 'UF', App\Models\Cidade::estados())->attrs(['class' => 'select2'])->value($transportadora['veiculo_uf']) !!}
        </div>
        <div class="col-md-2">
            {!! Form::tel('frete_quantidade', 'Quantidade')->attrs(['class' => ''])->value($transportadora['frete_quantidade']) !!}
        </div>
        <div class="col-md-2">
            {!! Form::text('frete_especie', 'Espécie')->attrs(['class' => ''])->value($transportadora['frete_quantidade']) !!}
        </div>
        <div class="col-md-2 mt-2">
            {!! Form::text('frete_peso_bruto', 'Peso bruto')->attrs(['class' => ''])->value($transportadora['frete_peso_bruto']) !!}
        </div>
        <div class="col-md-2  mt-2">
            {!! Form::text('frete_peso_liquido', 'Peso liquído')->attrs(['class' => ''])->value($transportadora['frete_peso_liquido']) !!}
        </div>
        <div class="col-md-2  mt-2">
            {!! Form::text('despesa_acessorias', 'Outras despesas')->attrs(['class' => 'moeda'])->value($transportadora['despesa_acessorias']) !!}
        </div>
        <div class="col-md-2  mt-2">
            {!! Form::text('vDesc', 'Desconto')->attrs(['class' => 'moeda'])
            ->value(isset($item) ? __moeda($item->vDesc) : $dadosNf['vDesc']) !!}
        </div>

        @else

        <div class="col-md-2">
            {!! Form::select('frete_tipo', 'Tipo de frete', ['' => 'Selecione'] + [0 => 'Emitente', 1 => 'Destinatário', 2 => 'Terceiros', 9 => 'Sem frete'])->attrs(['class' => 'form-select'])->value($item->frete_tipo) !!}
        </div>
        <div class="col-md-2">
            {!! Form::text('veiculo_placa', 'Placa')->attrs(['class' => 'placa'])->value($item->veiculo_placa) !!}
        </div>
        <div class="col-md-2">
            {!! Form::select('transportadora_uf', 'UF', App\Models\Cidade::estados())->attrs(['class' => 'select2'])->value($item->veiculo_uf) !!}
        </div>
        <div class="col-md-2">
            {!! Form::tel('frete_quantidade', 'Quantidade')->attrs(['class' => ''])->value($item->frete_quantidade) !!}
        </div>
        <div class="col-md-2">
            {!! Form::text('frete_especie', 'Espécie')->attrs(['class' => ''])->value($item->frete_especie) !!}
        </div>
        <div class="col-md-2 mt-2">
            {!! Form::text('frete_peso_bruto', 'Peso bruto')->attrs(['class' => ''])->value($item->frete_peso_bruto) !!}
        </div>
        <div class="col-md-2  mt-2">
            {!! Form::text('frete_peso_liquido', 'Peso liquído')->attrs(['class' => ''])->value($item->frete_peso_liquido) !!}
        </div>
        <div class="col-md-2  mt-2">
            {!! Form::text('despesa_acessorias', 'Outras despesas')->attrs(['class' => 'moeda'])->value($item->despesa_acessorias) !!}
        </div>
        <div class="col-md-2  mt-2">
            {!! Form::text('vDesc', 'Desconto')->attrs(['class' => 'moeda'])->value(isset($item) ? __moeda($item->vDesc) : $dadosNf['vDesc']) !!}
        </div>
        @endisset
    </div>
    <hr class="mt-4">
    <div class="row">
        <div class="col-6">
            {!! Form::textarea('motivo', 'Motivo')->attrs(['class' => '']) !!}
        </div>
        <div class="col-6">
            {!! Form::textarea('observacao', 'Observação')->attrs(['class' => '']) !!}
        </div>
    </div>

    @isset($dadosNf)
    <h4 class="mt-3">Valor integral da nota: <strong id=""> R$ {{ __moeda($dadosNf['vProd']) }}</strong></h4>
    <input type="hidden" name="valor_integral" value="{{ $dadosNf['vProd'] }}">
    @else
    <h4 class="mt-3">Valor integral da nota: <strong id=""> R$ {{ __moeda($item->valor_integral) }}</strong></h4>
    <input type="hidden" name="valor_integral" value="{{ $item->valor_integral }}">
    @endisset

    <div class="mt-3">
        <button type="submit" class="btn btn-info px-5">Salvar</button>
    </div>
</div>

@section('js')
<script src="/js/devolucao.js"></script>
@endsection
