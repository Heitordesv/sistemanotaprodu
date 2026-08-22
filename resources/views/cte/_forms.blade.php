@section('css')
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
@endsection

<input type="hidden" id="cte_id" value="{{{ isset($cte) ? $cte->id : 0}}}" name="">

@if(!isset($cte))
<form id="form-import" method="post" action="{{ route('cte.importarXml') }}" enctype="multipart/form-data">
    @csrf
    <div class="row">
        <div class="col-md-3 file-certificado">
            <span style="width: 100%" class="btn btn-dark btn-file">
                Importar XML<input accept=".xml" id="inp-xml" name="xml" type="file">
            </span>
        </div>
    </div>
    @if($errors->has('file'))
    <span class="text-danger">{{ $errors->first('file') }}</span>
    @endif
</form>
@endif

<br>
@isset($item)
{!!Form::open()->fill($item)
->put()
->route('cte.update', [$item->id])
->multipart()!!}
@else
{!!Form::open()
->post()
->route('cte.store')
->multipart()!!}
@endisset

<div class="row g-3">
    @if(!empresaComFilial())
    <div class="row">
        <div class="col-lg-4 col-md-4 col-sm-6">
            <h6>Ultima CTe: <strong>{{ $lastCte }}</strong></h6>
        </div>
        <div class="col-lg-4 col-md-4 col-sm-6">
            @if($config->ambiente == 2)
            <h6>Ambiente: <strong class="text-primary">Homologação</strong></h6>
            @else
            <h6>Ambiente: <strong class="text-success">Produção</strong></h6>
            @endif
        </div>
    </div>
    @endif
    @isset($item)
    {!! __view_locais_select_edit("Local", $item->filial_id) !!}
    @else
    {!! __view_locais_select() !!}
    @endif
    <div class="col-12">

    </div>
    <div class="col-md-4">
        {!! Form::select('natureza_id', 'Natureza de operação', ['' => 'Selecione'] + $naturezas->pluck('natureza', 'id')->all())
        ->attrs(['class' => 'select2 class-required'])->required()->value(isset($item) ? $item->natureza_id : '') !!}
    </div>
    <div class="col-md-2">
        {!! Form::select('tipo_servico', 'Tipo de serviço', App\Models\Cte::tiposServico())->attrs(['class' => 'select2']) !!}
    </div>

    <div class="col-md-2">
        {!! Form::select('globalizado', 'Tipo globalizado', [0 => 'Não', 1 => 'Sim'])->attrs(['class' => 'select2']) !!}
    </div>
    
    <div class="col-md-4">
        {!! Form::select('cst', 'CST', App\Models\Cte::getCsts())->attrs(['class' => 'select2']) !!}
    </div>
    <div class="col-md-1">
        {!! Form::text('perc_icms', '%ICMS')->required()->attrs(['class' => 'perc class-required'])->value(isset($item) ? $item->perc_icms : '' ) !!}
    </div>
    <div class="col-md-1">
        {!! Form::text('pRedBC', '%RED. BC') !!}
    </div>
    <div class="col-md-6">
        {!! Form::select('remetente_id','Remetente', ['' => 'Selecione'] + $clientes->pluck('razao_social', 'id')->all(),
        )->attrs(['class' => 'select2 class-required'])->required()
        ->value(isset($item) ? $item->remetente_id : null) !!}
        <div class="card mt-3 div-remetente d-none">
            <div class="m-3">
                <h5 style="color: rgb(13, 197, 13)" class="text-center">REMETENTE SELECIONADO</h5>
                <hr>
                <H6>Razão Social: <strong id="razao_social_remetente"></strong></H6>
                <H6>CNPJ: <strong id="cnpj_remetente"></strong></H6>
                <H6>Cidade: <strong id="cidade_remetente"></strong></H6>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        {!! Form::select(
        'destinatario_id',
        'Destinatário',
        ['' => 'Selecione'] + $clientes->pluck('razao_social', 'id')->all(),
        )->attrs(['class' => 'select2 class-required'])->required()
        ->value(isset($item) ? $item->destinatario_id : null) !!}
        <div class="card mt-3 div-destinatario d-none">
            <div class="m-3">
                <h5 style="color: rgb(13, 197, 13)" class="text-center">DESTINÁTARIO SELECIONADO</h5>
                <hr>
                <H6>Razão Social: <strong id="razao_social_destinatario"></strong></H6>
                <H6>CNPJ: <strong id="cnpj_destinatario"></strong></H6>
                <H6>Cidade: <strong id="cidade_destinatario"></strong></H6>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        {!! Form::select(
        'expedidor_id',
        'Expedidor',
        ['' => 'Selecione'] + $clientes->pluck('razao_social', 'id')->all(),
        )->attrs(['class' => 'select2'])
        ->value(isset($item) ? $item->expedidor_id : null) !!}
        <div class="card mt-3 div-expedidor d-none">
            <div class="m-3">
                <h5 style="color: rgb(13, 197, 13)" class="text-center">EXPEDIDOR SELECIONADO</h5>
                <hr>
                <H6>Razão Social: <strong id="razao_social_expedidor"></strong></H6>
                <H6>CNPJ: <strong id="cnpj_expedidor"></strong></H6>
                <H6>Cidade: <strong id="cidade_expedidor"></strong></H6>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        {!! Form::select(
        'recebedor_id',
        'Recebedor',
        ['' => 'Selecione'] + $clientes->pluck('razao_social', 'id')->all(),
        )->attrs(['class' => 'select2'])
        ->value(isset($item) ? $item->recebedor_id : null) !!}
        <div class="card mt-3 div-recebedor d-none">
            <div class="m-3">
                <h5 style="color: rgb(13, 197, 13)" class="text-center">RECEBEDOR SELECIONADO</h5>
                <hr>
                <H6>Razão Social: <strong id="razao_social_recebedor"></strong></H6>
                <H6>CNPJ: <strong id="cnpj_recebedor"></strong></H6>
                <H6>Cidade: <strong id="cidade_recebedor"></strong></H6>
            </div>
        </div>
    </div>
    <hr>
    <div class="card border-top border-0 border-4 border-primary">
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
                        @foreach ($item->chaves_nfe as $i)
                        <tr class="dynamic-form">
                            <td>
                                <input type="tel" id="chave_nfe" class="form-control class-required" name="chave_nfe[]" value="{{$i->chave}}">
                            </td>
                            <td>
                                <button type="button" class="btn btn-danger btn-sm btn-remove-tr">
                                    <i class="bx bx-trash"></i>
                                </button>
                            </td>
                        </tr>
                        @endforeach
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
                ])->attrs(['class' => 'select2 class-outros class-required'])
                ->value(isset($item) ? $item->tpDoc : '') !!}
            </div>
            <div class="col-md-3">
                {!! Form::text('descOutros', 'Descrição doc.')->attrs(['class' => 'class-outros class-required'])->value(isset($item) ? $item->descOutros : '' ) !!}
            </div>
            <div class="col-md-3">
                {!! Form::text('nDoc', 'Número doc.')->attrs(['class' => 'class-outros class-required'])->value(isset($item) ? $item->nDoc : '' ) !!}
            </div>
            <div class="col-md-3">
                {!! Form::tel('vDocFisc', 'Valor doc.')->attrs(['class' => 'moeda class-outros class-required'])->value(isset($item) ? __moeda($item->vDocFisc) : '' ) !!}
            </div>
        </div>
    </div>

    <div class="row g-3">
        <hr>
        <h3>Informações da Carga</h3>
        <div class="col-md-2">
            {!! Form::select('veiculo_id', 'Veículo', ['' => 'Selecione'] + $veiculos->pluck('placa', 'id')
            ->all())->attrs(['class' => 'select2'])->value(isset($item) ? $item->veiculo_id : '' )
            ->required() !!}
        </div>
        <div class="col-md-3">
            {!! Form::text('produto_predominante', 'Produto predominante')->required()->value(isset($item) ? $item->produto_predominante : '' ) !!}
        </div>
        <div class="col-md-2">
            {!! Form::select('tomador', 'Tomador', App\Models\Cte::tiposTomador())->attrs(['class' => 'select2'])->value(isset($item) ? $item->tomador : '')
            ->required() !!}
        </div>
        <div class="col-md-2">
            {!! Form::tel('valor_carga', 'Valor carga')->attrs(['class' => 'moeda'])->value(isset($item) ? $item->valor_carga : '')
            ->required() !!}
        </div>
        <div class="col-md-3">
            {!! Form::select('modal', 'Modelo de transporte',
            App\Models\Cte::modals())->attrs(['class' => 'select2'])->required()->value(isset($item) ? $item->modal : '') !!}
        </div>
    </div>
    <hr>
    <div class="table-responsive">
        <h5>Informações de quantidade</h5>
        <div class="row">
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
                            {!! Form::tel('quantidade_carga[]', '')->attrs(['class' => 'moeda'])->required() !!}
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
    <hr>
    <div class="table-responsive">
        <h5>Componentes de carga</h5>
        <p class="mt-1" style="color: crimson">*A soma dos valores dos componentes deve ser igual ao valor a receber!</p>
        <div class="row">
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
                            {!! Form::text('nome_componente[]', '')->required() !!}
                        </td>
                        <td class="col-md-4">
                            {!! Form::text('valor_componente[]', '')->attrs(['class' => 'moeda'])->required() !!}
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
    <hr>
    <h4>Informação de entrega</h4>
    <h6 class="mt-2" style="color: cornflowerblue">Endereço do tomador</h6>
    <div class="col-md-9">
        {!! Form::radio('tipo', 'Endereço do destinatário')
        ->value('destinatario')->required()->checked(isset($item) ? $item->tomador == 3 : false) !!}
    </div>
    <div class="col-md-9 mt-1">
        {!! Form::radio('tipo', 'Endereço do remetente')
        ->value('remetente')->required()->checked(isset($item) ? $item->tomador == 0 : false) !!}
    </div>
    <div class="col-md-6">
        {!! Form::text('logradouro_tomador', 'Rua')->required()->value(isset($item) ? $item->logradouro_tomador : '') !!}
    </div>
    <div class="col-md-2">
        {!! Form::text('numero_tomador', 'Número')->required()->value(isset($item) ? $item->numero_tomador : '') !!}
    </div>
    <div class="col-md-2">
        {!! Form::text('cep_tomador', 'CEP')->attrs(['class' => 'cep'])->required()->value(isset($item) ? $item->cep_tomador : '') !!}
    </div>
    <div class="col-md-5">
        {!! Form::text('bairro_tomador', 'Bairro')->required()->value(isset($item) ? $item->bairro_tomador : '') !!}
    </div>
    <div class="col-md-5">
        {!! Form::select('municipio_tomador', 'Cidade', ['' => 'Selecione'] + $cidades->pluck('nome', 'id')->all())->attrs([
        'class' => 'select2',
        ])->required()->value(isset($item) ? $item->municipio_tomador : '') !!}
    </div>
    <div class="col-md-3">
        {!! Form::date('data_prevista_entrega', 'Data prevista de entrega')->required()->value(isset($item) ? $item->data_prevista_entrega : '') !!}
    </div>

    <div class="col-md-3">
        {!! Form::tel('valor_transporte', 'Valor da prestação de serviço')->required()->attrs(['class' => 'moeda'])->value(isset($item) ? __moeda($item->valor_transporte) : '')  !!}
    </div>
    <div class="col-md-2">
        {!! Form::tel('valor_receber', 'Valor a receber')->attrs(['class' => 'moeda'])->required()->value(isset($item) ? __moeda($item->valor_receber) : '') !!}
    </div>
    <div class="col-md-4">
        {!! Form::select('municipio_envio', 'Município de envio', ['' => 'Selecione'] + $cidades->pluck('nome', 'id')->all())->attrs([
        'class' => 'select2',
        ])->required()->value(isset($item) ? $item->municipio_envio : '') !!}
    </div>
    <div class="col-md-4">
        {!! Form::select('municipio_inicio', 'Município de início', ['' => 'Selecione'] + $cidades->pluck('nome', 'id')->all())->attrs([
        'class' => 'select2',
        ])->required()->value(isset($item) ? $item->municipio_inicio : '') !!}
    </div>
    <div class="col-md-4">
        {!! Form::select('municipio_fim', 'Município final', ['' => 'Selecione'] + $cidades->pluck('nome', 'id')->all())->attrs([
        'class' => 'select2',
        ])->required()->value(isset($item) ? $item->municipio_fim : '') !!}
    </div>
    <div class="col-md-2">
        {!! Form::select('retira', 'Retira', [1 => 'Sim', 0 => 'Não'])->attrs(['class' => 'select2'])->value(isset($item) ? $item->retira : '') !!}
    </div>
    <div class="col-md-10">
        {!! Form::text('detalhes_retira', 'Detalhes (opcional)')->value(isset($item) ? $item->detalhes_retira : '') !!}
    </div>
    <div class="col-md-12">
        {!! Form::text('observacao', 'Informação adicional')->value(isset($item) ? $item->observacao : '') !!}
    </div>
    <div class="col-12 alerts mt-4">
    </div>
    <div class="col-12 mt-5">
        <button type="submit" disabled class="btn btn-primary px-5 btn-salvarCte">Salvar</button>
    </div>
</div>
{!!Form::close()!!}
@section('js')
<script src="/js/cte.js"></script>
@endsection
