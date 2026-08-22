@extends('default.layout', ['title' => 'Nova CTe com XML'])
@section('content')

<style type="text/css">
    .btn-file {
        position: relative;
        overflow: hidden;
    }

    .btn-file input[type=file] {
        position: absolute;
        top: 0;
        right: 0;
        min-width: 100%;
        min-height: 100%;
        font-size: 100px;
        text-align: right;
        filter: alpha(opacity=0);
        opacity: 0;
        outline: none;
        background: white;
        cursor: inherit;
        display: block;
    }

</style>


<div class="page-content">
    <div class="card border-top border-0 border-4 border-primary">
        <div class="card-body p-5">
            <div class="page-breadcrumb d-sm-flex align-items-center mb-3">
                <div class="ms-auto">
                    <a href="{{ route('cte.index')}}" type="button" class="btn btn-light btn-sm">
                        <i class="bx bx-arrow-back"></i> Voltar
                    </a>
                </div>
            </div>
            <div class="card-title d-flex align-items-center">
                <h5 class="mb-0 text-primary">Emissão CTe com importação de xml</h5>
            </div>
            <hr>
            <div class="pl-lg-4">
                {!!Form::open()
                ->post()
                ->route('cte.store')
                !!}
                <input type="hidden" name="validate" value="0">
                {{-- aqui --}}
                <div class="pl-lg-4">
                    <input type="hidden" id="clientes" value="{{json_encode($clientes)}}" name="">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="col-form-label required">Natureza de Operação</label>
                            <div class="input-group date">
                                <select class="custom-select form-select" id="inp-natureza_id" name="natureza_id">
                                    @foreach($naturezas as $n)
                                    <option @if($config->nat_op_padrao == $n->id)
                                        selected
                                        @endif
                                        value="{{$n->id}}">{{$n->natureza}}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="form-group validated col-sm-3 col-lg-2 col-12">
                            <label class="col-form-label" id="">Tipo globalizado</label>
                            <select class="custom-select form-select" id="globalizado" name="globalizado">
                                <option @if(isset($cte)) @if($cte->globalizado == 0)
                                    selected
                                    @endif
                                    @endif value="0">Não</option>
                                <option @if(isset($cte)) @if($cte->globalizado == 1)
                                    selected
                                    @endif
                                    @endif value="1">Sim</option>
                            </select>
                        </div>

                        @if($tributacao->regime == 1)
                        <div class="form-group validated col-sm-3 col-lg-3 col-12">
                            <label class="col-form-label required">CST</label>
                            <select class="custom-select form-select" id="cst" name="cst">
                                @foreach(App\Models\Cte::getCsts() as $key => $c)
                                <option @if(isset($cte)) @if($key==$cte->cst) selected @endif @endif value="{{$key}}">{{$c}}</option>
                                @endforeach
                            </select>
                        </div>
                        @else
                        <div class="form-group validated col-sm-3 col-lg-3 col-12">
                            <label class="col-form-label">Contribuinte</label>
                            <select class="custom-select form-select" id="cst" name="cst">
                                @foreach(App\Models\Cte::getCsosn() as $key => $c)
                                <option @if(isset($cte)) @if($key==$cte->cst) selected @endif @endif value="{{$key}}">{{$c}}</option>
                                @endforeach
                            </select>
                        </div>
                        @endif

                        <div class="form-group col-sm-2 col-lg-2 col-12">
                            <label class="col-form-label required">%ICMS</label>
                            <div class="col-sm-12">
                                <div class="input-group">
                                    <input type="text" name="perc_icms" class="form-control perc" value="@if(isset($cte)) {{$cte->perc_icms}} @else 0 @endif" id="inp-perc_icms" />
                                </div>
                            </div>
                        </div>

                        <div class="form-group col-sm-2 col-lg-2 col-12">
                            <label class="col-form-label">%Red. BC</label>
                            <div class="col-sm-12">
                                <div class="input-group">
                                    <input type="text" name="pRedBC" class="form-control perc" value="@if(isset($cte)) {{$cte->pRedBC}} @else 0 @endif" id="pRedBC" />
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="form-group validated col-sm-6 col-lg-6 col-12 mt-3">
                            <label class="col-form-label required" id="">Remetente</label><br>
                            <select class="form-control select2" style="width: 100%" id="inp-remetente_id" name="remetente_id">
                                <option value="null">Selecione o Remetente</option>
                                @foreach($clientes as $c)
                                <option @if($dadosDaNFe['remetente']==$c->id) selected @endif value="{{$c->id}}">{{$c->id}} - {{$c->razao_social}} ({{$c->cpf_cnpj}})</option>
                                @endforeach
                            </select>
                            <hr>
                            <div class="row" id="info-remetente" style="display: block">
                                <div class="col-xl-12">
                                    <div class="card border-top border-0 border-4 border-primary">
                                        <div class="card-body">
                                            <h4 class="center-align">REMENTE SELECIONADO</h4>
                                            <H6>Razão Social: <strong id="razao_social_remetente"></strong></H6>
                                            <H6>CNPJ: <strong id="cnpj_remetente"></strong></H6>
                                            <H6>Cidade: <strong id="cidade_remetente"></strong></H6>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-group validated col-sm-6 col-lg-6 col-12 mt-3">
                            <label class="col-form-label required" id="">Destinatário</label><br>
                            <select class="form-control select2" style="width: 100%" id="inp-destinatario_id" name="destinatario_id">
                                <option value="null">Selecione o Destinatário</option>
                                @foreach($clientes as $c)
                                <option @if($dadosDaNFe['destinatario']==$c->id) selected @endif value="{{$c->id}}">{{$c->id}} - {{$c->razao_social}} ({{$c->cpf_cnpj}})</option>
                                @endforeach
                            </select>
                            <hr>
                            <div class="row" id="info-destinatario" style="display: block">
                                <div class="col-xl-12">
                                    <div class="card border-top border-0 border-4 border-primary">
                                        <div class="card-body">
                                            <h4 class="center-align">DESTINÁTARIO SELECIONADO</h4>
                                            <H6>Razão Social: <strong id="razao_social_destinatario"></strong></H6>
                                            <H6>CNPJ: <strong id="cnpj_destinatario"></strong></H6>
                                            <H6>Cidade: <strong id="cidade_destinatario"></strong></H6>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-group validated col-sm-6 col-lg-6 col-12">
                            <label class="col-form-label" id="">Expedidor</label>
                            <select class="form-control select2" style="width: 100%" id="kt_select2_10" name="cliente">
                                <option value="null">Selecione o Expedidor</option>
                                @foreach($clientes as $c)
                                <option @if(isset($cte)) @if($cte->expedidor_id == $c->id) selected @endif @endif value="{{$c->id}}">{{$c->razao_social}} ({{$c->cpf_cnpj}})</option>
                                @endforeach
                            </select>
                            <hr>
                            <div class="row" id="info-expedidor" style="display: none">
                                <div class="col-xl-12">
                                    <div class="card card-custom gutter-b">
                                        <div class="card-body">
                                            <H6>Razão Social: <strong id="razao_social_expedidor"></strong></H6>
                                            <H6>CNPJ: <strong id="cnpj_expedidor"></strong></H6>
                                            <H6>Cidade: <strong id="cidade_expedidor"></strong></H6>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-group validated col-sm-6 col-lg-6 col-12">
                            <label class="col-form-label" id="">Recebedor</label>
                            <div class="input-group">
                                <div class="input-group-prepend w-100">
                                    <select class="form-control select2" style="width: 100%" id="kt_select2_11" name="cliente">
                                        <option value="null">Selecione o Recebedor</option>
                                        @foreach($clientes as $c)
                                        <option @if(isset($cte)) @if($cte->recebedor_id == $c->id) selected @endif @endif value="{{$c->id}}">{{$c->razao_social}} ({{$c->cpf_cnpj}})</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <hr>
                            <div class="row" id="info-recebedor" style="display: none">
                                <div class="col-xl-12">
                                    <div class="card card-custom gutter-b">
                                        <div class="card-body">
                                            <H6>Razão Social: <strong id="razao_social_recebedor"></strong></H6>
                                            <H6>CNPJ: <strong id="cnpj_recebedor"></strong></H6>
                                            <H6>Cidade: <strong id="cidade_recebedor"></strong></H6>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="row g-3 row m-3">
                        <h3>Referência de Documento para CTe</h3>
                        <div class="row m-3">
                            <div class="col-md-6 row">
                                <button type="button" class="btn btn-outline-primary btn-nfe link-active px-6" onclick="selectDiv('nfe')">NFe</button>
                            </div>
                            <div class="col-md-6 row m-auto">
                                <button type="button" class="btn btn-outline-primary btn-outros" onclick="selectDiv('outros')">Outros</button>
                            </div>
                        </div>
                    </div>
                    <input type="hidden" id="chave_import" value="{{$dadosDaNFe['chave']}}" name="">
                    <div class="div-nfe row g-3 table-responsive m-3">
                        <h6>Chaves da NFe</h6>
                        <div class="row">
                            <table class="table table-dynamic table-chave">
                                <thead>
                                    <tr>
                                        <th>Chave</th>
                                        <th>Ação</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if(isset($item) && sizeof($item->chaves_nfe) > 0)
                                    <tr class="dynamic-form">
                                        <td>
                                            <input type="tel" id="chave_nfe" class="form-control class-required" name="chave_nfe[]" value="{{$item->chave}}">
                                        </td>
                                        <td>
                                            <button type="button" class="btn btn-danger btn-sm btn-remove-tr">
                                                <i class="bx bx-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    @else
                                    <tr class="dynamic-form">
                                        <td>
                                            <input type="tel" id="chave_nfe" class="form-control class-required" name="chave_nfe[]">
                                        </td>
                                        <td>
                                            <button type="button" class="btn btn-danger btn-sm btn-remove-tr">
                                                <i class="bx bx-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                        <div class="row">
                            <div class="col-12">
                                <button type="button" class="btn btn-success btn-add-tr">
                                    Adicionar chave
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="div-outros row d-none g-3 m-3">
                        <h5>Outros</h5>
                        <div class="col-md-3">
                            {!! Form::select('tpDoc', 'Tipo', [null => 'Selecione'] + [
                            '00' => 'Declaração',
                            '10' => 'Dutoviário',
                            '59' => 'Cf-e SAT',
                            '65' => 'NFCe',
                            '99' => 'Outros',
                            ])->attrs(['class' => 'select2 class-outros class-required']) !!}
                        </div>
                        <div class="col-md-3">
                            {!! Form::text('descOutros', 'Descrição doc.')->attrs(['class' => 'class-outros class-required']) !!}
                        </div>
                        <div class="col-md-3">
                            {!! Form::text('nDoc', 'Número doc.')->attrs(['class' => 'class-outros class-required']) !!}
                        </div>
                        <div class="col-md-3">
                            {!! Form::tel('vDocFisc', 'Valor doc.')->attrs(['class' => 'moeda class-outros class-required']) !!}
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-sm-12 col-lg-12 col-md-12 col-xl-12">
                        <hr>
                        <h3>Informações da Carga</h3>
                        <div class="row">
                            <div class="form-group validated col-sm-2 col-lg-2 col-12 mt-3">
                                {!! Form::select('veiculo_id', 'Veículo', ['' => 'Selecione'] + $veiculos->pluck('placa', 'id')
                                ->all())->attrs(['class' => 'select2'])
                                ->required() !!}
                            </div>
                            <div class="form-group col-sm-3 col-lg-3 col-12">
                                <label class="col-form-label required">Produto predominante</label>
                                <div class="">
                                    <div class="input-group">
                                        <input type="text" name="produto_predominante" class="form-control type-ref" value="{{$dadosDaNFe['produto_predominante']}}" id="prod_predominante" />
                                    </div>
                                </div>
                            </div>
                            <div class="form-group validated col-sm-2 col-lg-2 col-12">
                                <label class="col-form-label required" id="">Tomador</label>
                                <select class="custom-select form-select" id="tomador" name="tomador">
                                    @foreach($tiposTomador as $key => $t)
                                    <option value="{{$key}}">{{ $key . " - " .$t }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group col-sm-2 col-lg-2 col-12">
                                <label class="col-form-label required">Valor da carga</label>
                                <div class="">
                                    <div class="input-group">
                                        <input type="text" name="valor_carga" class="form-control moeda" value="{{ __moeda($dadosDaNFe['valor_carga'])}}" id="valor_carga" />
                                    </div>
                                </div>
                            </div>
                            <div class="form-group validated col-sm-3 col-lg-3 col-12">
                                <label class="col-form-label required" id="">Modelo de transporte</label>
                                <select class="custom-select form-select" id="modal-transp" name="modal">
                                    @foreach($modals as $key => $t)
                                    <option value="{{$key}}">{{ $key . " - " . $t }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <hr class="mt-4">

                        <div class="col-sm-12 col-lg-12 col-md-12 col-xl-12">
                            <h3>Informações de quantidade</h3>
                            <div class="row">
                                <div class="table-responsive">
                                    <table class="table table-striped table-informacoes table-dynamic" id="prod">
                                        <thead>
                                            <tr>
                                                <th>Unidade</th>
                                                <th>Tipo de medida</th>
                                                <th>Quantidade</th>
                                                <th>Ação</th>
                                            </tr>
                                        </thead>
                                        <tbody id="body" class="datatable-body">
                                            @if(isset($item) && sizeof($item->medidas) > 0)
                                            @foreach($item->medidas as $med)
                                            <tr class="dynamic-form">
                                                <td class="col-md-3">
                                                    {!! Form::select('cod_unidade[]', '', $unidadesMedida)->attrs(['class' => 'select2'])
                                                    ->required()->value($med->cod_unidade) !!}
                                                </td>
                                                <td class="col-md-4">
                                                    {!! Form::select('tipo_medida[]', '', $tiposMedida)->attrs(['class' => 'select2'])
                                                    ->required()->value($med->tipo_medida) !!}
                                                </td>
                                                <td class="col-md-2">
                                                    {!! Form::tel('quantidade_carga[]', '')->attrs(['class' => 'moeda'])
                                                    ->required()->value(__moeda($med->quantidade_carga)) !!}
                                                </td>
                                                <td>
                                                    <br>
                                                    <button class="btn btn-danger btn-sm btn-remove-tr">
                                                        <i class="bx bx-trash"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                            @endforeach
                                            @else
                                            <tr class="dynamic-form">
                                                <td class="col-md-3">
                                                    {!! Form::select('cod_unidade[]', '', $unidadesMedida)->attrs(['class' => 'select2'])->required() !!}
                                                </td>
                                                <td class="col-md-4">
                                                    {!! Form::select('tipo_medida[]', '', $tiposMedida)->attrs(['class' => 'select2'])->required() !!}
                                                </td>
                                                <td class="col-md-2">
                                                    {!! Form::tel('quantidade_carga[]', '')->attrs(['class' => 'moeda'])->required()->value($dadosDaNFe['quantidade']) !!}
                                                </td>
                                                <td>
                                                    <br>
                                                    <button class="btn btn-danger btn-sm btn-remove-tr">
                                                        <i class="bx bx-trash"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                            @endif
                                        </tbody>
                                    </table>
                                </div>
                                <div class="row">
                                    <div class="col-12">
                                        <button type="button" class="btn btn-success btn-add-tr">
                                            Adicionar
                                        </button>
                                    </div>
                                </div>
                                <br>
                            </div>
                        </div>
                        <hr class="mt-4">
                        <div class="col-sm-12 col-lg-12 col-md-12 col-xl-12">
                            <h3>Componentes da carga</h3>
                            <div class="row">
                                <div class="table-responsive">
                                    <table class="table table-striped table-componentes table-dynamic" id="componentes">
                                        <thead>
                                            <tr>
                                                <th>Nome do componente</th>
                                                <th>Valor</th>
                                                <th>Ação</th>
                                            </tr>
                                        </thead>
                                        <tbody id="body" class="datatable-body">
                                            @if(isset($item) && sizeof($item->componentes) > 0)
                                            @foreach($item->componentes as $cp)
                                            <tr class="dynamic-form">
                                                <td class="col-md-5">
                                                    {!! Form::text('nome_componente[]', '')->required()->value($cp->nome) !!}
                                                </td>
                                                <td class="col-md-4">
                                                    {!! Form::text('valor_componente[]', '')->attrs(['class' => 'moeda'])->required()
                                                    ->value(__moeda($cp->valor))
                                                    !!}
                                                </td>
                                                <td>
                                                    <br>
                                                    <button class="btn btn-danger btn-sm btn-remove-tr">
                                                        <i class="bx bx-trash"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                            @endforeach
                                            @else
                                            <tr class="dynamic-form">
                                                <td class="col-md-5">
                                                    {!! Form::text('nome_componente[]', '')->required()->value('frete') !!}
                                                </td>
                                                <td class="col-md-4">
                                                    {!! Form::text('valor_componente[]', '')->attrs(['class' => 'moeda'])->required()->value(__moeda($dadosDaNFe['valor_frete'])) !!}
                                                </td>
                                                <td>
                                                    <br>
                                                    <button class="btn btn-danger btn-sm btn-remove-tr">
                                                        <i class="bx bx-trash"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                            @endif
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-12">
                                    <button type="button" class="btn btn-success btn-add-tr">
                                        Adicionar
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <hr class="mt-4">
                    <div class="row">
                        <div class="row">
                            <h3>Informações da Entrega</h3>
                            <h6 class="text-info mt-3">Endereço do tomador</h6>
                            <div class="col-md-9 mt-3">
                                {!! Form::radio('tipo', 'Endereço do destinatário')
                                ->value('destinatario')->required()->checked(isset($item) ? $item->tomador == 3 : false) !!}
                            </div>
                            <div class="col-md-9 mt-1">
                                {!! Form::radio('tipo', 'Endereço do remetente')
                                ->value('remetente')->required()->checked(isset($item) ? $item->tomador == 0 : false) !!}
                            </div>
                            <div class="col-md-6 mt-3">
                                {!! Form::text('logradouro_tomador', 'Rua')->required() !!}
                            </div>
                            <div class="col-md-2 mt-3">
                                {!! Form::text('numero_tomador', 'Número')->required() !!}
                            </div>
                            <div class="col-md-2 mt-3">
                                {!! Form::text('cep_tomador', 'CEP')->attrs(['class' => 'cep'])->required() !!}
                            </div>
                            <div class="col-md-5 mt-3">
                                {!! Form::text('bairro_tomador', 'Bairro')->required() !!}
                            </div>
                            <div class="col-md-5 mt-3">
                                {!! Form::select('municipio_tomador', 'Cidade', ['' => 'Selecione'] + $cidades->pluck('nome', 'id')->all())->attrs([
                                'class' => 'select2',
                                ])->required() !!}
                            </div>
                            <div class="col-md-3 mt-3">
                                {!! Form::date('data_prevista_entrega', 'Data prevista de entrega')->required() !!}
                            </div>

                            <div class="col-md-3 mt-3">
                                {!! Form::tel('valor_transporte', 'Valor da prestação de serviço')->required()->attrs(['class' => 'moeda'])->value(__moeda($dadosDaNFe['valor_frete'])) !!}
                            </div>
                            <div class="col-md-2 mt-3">
                                {!! Form::tel('valor_receber', 'Valor a receber')->attrs(['class' => 'moeda'])->required()->value(__moeda($dadosDaNFe['valor_frete'])) !!}
                            </div>
                            <div class="col-md-4 mt-3">
                                {!! Form::select('municipio_envio', 'Município de envio', ['' => 'Selecione'] + $cidades->pluck('nome', 'id')->all())->attrs([
                                'class' => 'select2',
                                ])->required() !!}
                            </div>
                            <div class="col-md-4 mt-3">
                                {!! Form::select('municipio_inicio', 'Município de início', ['' => 'Selecione'] + $cidades->pluck('nome', 'id')->all())->attrs([
                                'class' => 'select2',
                                ])->required() !!}
                            </div>
                            <div class="col-md-4 mt-3">
                                {!! Form::select('municipio_fim', 'Município final', ['' => 'Selecione'] + $cidades->pluck('nome', 'id')->all())->attrs([
                                'class' => 'select2',
                                ])->required() !!}
                            </div>
                            <div class="col-md-2 mt-3">
                                {!! Form::select('retira', 'Retira', [1 => 'Sim', 0 => 'Não'])->attrs(['class' => 'select2']) !!}
                            </div>
                            <div class="col-md-10 mt-3">
                                {!! Form::text('detalhes_retira', 'Detalhes (opcional)') !!}
                            </div>
                            <div class="col-md-12 mt-3">
                                {!! Form::text('observacao', 'Informação adicional') !!}
                            </div>
                            <div class="col-12 alerts mt-4">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-3 col-lg-3 col-md-3 col-xl-3 col-12 mt-4">
                    <button style="width: 100%; margin-top: 15px;" type="submit" class="btn btn-success">Salvar</button>
                </div>
            </div>
        </div>

        {{-- fim --}}
        {!!Form::close()!!}

    </div>
</div>
</div>
</div>


@section('js')
<script src="/js/cte.js"></script>
@endsection




@endsection
