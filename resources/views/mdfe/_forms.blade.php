<div class="row g-3">
    @if(!isset($item))
    <h6>Ultima MDFe: <strong>{{ $lastMdfe }}</strong></h6>
    @endif
    <h5>Dados iniciais</h5>
    <hr>
    @if(!empresaComFilial())
    <div class="row">
        <div class="col-lg-4 col-md-4 col-sm-6">
            @if(!isset($item))
            <h6>Ultima MDFe: <strong>{{$lastMdfe}}</strong></h6>
            @endif
        </div>
        <div class="col-lg-4 col-md-4 col-sm-6">
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
    <div class="col-md-2">
        {!! Form::select('uf_inicio', 'UF inicial', ['' => 'Selecione...'] + App\Models\Cidade::estados())->attrs([
        'class' => 'select2',
        ])->required() !!}
    </div>
    <div class="col-md-2">
        {!! Form::select('uf_fim', 'UF final', ['' => 'Selecione...'] + App\Models\Cidade::estados())->attrs([
        'class' => 'select2',
        ])->required() !!}
    </div>
    <div class="col-md-3">
        {!! Form::date('data_inicio_viagem', 'Data início da viagem')->value(date('Y-m-d'))->required() !!}
    </div>
    <div class="col-md-2">
        {!! Form::select('carga_posterior', 'Carga posterior', [0 => 'Não', 1 => 'Sim'])->attrs(
        ['class' => 'select2'],
        )->required() !!}
    </div>
    <div class="col-md-3">
        {!! Form::select(
        'tp_emit',
        'Tipo do emitente',
        ['' => 'Selecione...'] + [
        1 => 'Prestador de serviço de transporte',
        2 => 'Transportador de carga própria',
        ],
        )->attrs(['class' => 'select2 class-required'])->required() !!}
    </div>
    <div class="col-md-3">
        {!! Form::select(
        'tp_transp',
        'Tipo do transportador',
        ['' => 'Selecione...'] + [1 => 'ETC', 2 => 'TAC', 3 => 'CTC'],
        )->attrs(['class' => 'select2'])->required() !!}
    </div>
    <div class="col-md-2">
        {!! Form::text('lac_rodo', 'Lacre rodoviário')->attrs(['data-mask' => '00000000'])->required() !!}
    </div>
    <div class="col-md-3">
        {!! Form::tel('cnpj_contratante', 'CNPJ do contratante')->attrs(['class' => 'cpf_cnpj'])->required() !!}
    </div>
    <div class="col-md-2">
        {!! Form::tel('quantidade_carga', 'Quantidade da carga')->attrs(['class' => 'qtd_carga'])->required() !!}
    </div>
    <div class="col-md-2">
        {!! Form::tel('valor_carga', 'Valor da carga')->attrs(['class' => 'moeda'])->required() !!}
    </div>
    <div class="col-md-3">
        {!! Form::select(
        'veiculo_tracao_id',
        'Veículo de tração',
        ['' => 'Selecione...'] + $veiculos->pluck('placa', 'id')->all(),
        )->attrs([
        'class' => 'select2 class-required',
        ])->required() !!}
    </div>
    <div class="col-md-3">
        {!! Form::select(
        'veiculo_reboque_id',
        'Veículo de reboque 1 (opcional)',
        ['' => 'Selecione...'] + $veiculos->pluck('placa', 'id')->all(),
        )->attrs(['class' => 'select2']) !!}
    </div>
    <div class="col-md-3">
        {!! Form::select(
        'veiculo_reboque2_id',
        'Veículo de reboque 2 (opcional)',
        ['' => 'Selecione...'] + $veiculos->pluck('placa', 'id')->all(),
        )->attrs(['class' => 'select2']) !!}
    </div>
    <div class="col-md-3">
        {!! Form::select(
        'veiculo_reboque3_id',
        'Veículo de reboque 3 (opcional)',
        ['' => 'Selecione...'] + $veiculos->pluck('placa', 'id')->all(),
        )->attrs(['class' => 'select2']) !!}
    </div>
    <hr>
    <div class="row card-body g-3 m-1">
        <h6>Produto predominante (opcional)</h6>
        <div class="col-md-6">
            {!! Form::text('produto_pred_nome', 'Nome')->attrs(['class' => '']) !!}
        </div>
        <div class="col-md-2">
            {!! Form::tel('produto_pred_ncm', 'Ncm')->attrs(['class' => 'ncm']) !!}
        </div>
        <div class="col-md-3">
            {!! Form::tel('produto_pred_cod_barras', 'Código de barras')->attrs(['class' => '']) !!}
        </div>
        <div class="col-md-2">
            {!! Form::tel('cep_carrega', 'Cep carrega')->attrs(['class' => 'cep']) !!}
        </div>
        <div class="col-md-2">
            {!! Form::tel('latitude_carregamento', 'Latitude carrega')->attrs(['class' => '']) !!}
        </div>
        <div class="col-md-3">
            {!! Form::tel('longitude_carregamento', 'Longitude carrega')->attrs(['class' => '']) !!}
        </div>
        <div class="col-md-2">
            {!! Form::tel('cep_descarrega', 'Cep descarrega')->attrs(['class' => 'cep']) !!}
        </div>
        <div class="col-md-2">
            {!! Form::tel('latitude_descarregamento', 'Latitude descarrega')->attrs(['class' => '']) !!}
        </div>
        <div class="col-md-3">
            {!! Form::tel('longitude_descarregamento', 'Longitude descarrega')->attrs(['class' => '']) !!}
        </div>
        <div class="col-md-4">
            {!! Form::select('tp_carga', 'Tipo de carga', ['' => 'Selecione...'] + App\Models\Mdfe::tiposCarga())->attrs([
            'class' => 'select2',
            ]) !!}
        </div>
    </div>
    <hr>
    <div class="row mt-4">
        <div class="col-md-4 row">
            <button type="button" class="btn btn-outline-primary btn-gerais active px-6" onclick="selectDiv2('gerais')">INFORMAÇÕES GERAIS</button>
        </div>
        <div class="col-md-4 row ms-auto">
            <button type="button" class="btn btn-outline-primary btn-transporte px-6" onclick="selectDiv2('transporte')">
                INFORMAÇÕES TRANSPORTE</button>
        </div>
        <div class="col-md-4 row m-auto">
            <button type="button" class="btn btn-outline-primary btn-descarregamento px-6" onclick="selectDiv2('descarregamento')">
                INFORMAÇÕES DESCARREGAMENTO</button>
        </div>
    </div>
    <hr>
    {{-- div informações gerais --}}
    <div class="div-gerais row">
        <div class="card">
            <div class="row m-3">
                <h6>Seguradora (opcional)</h6>
                <div class="col-md-8 mt-3">
                    {!! Form::text('seguradora_nome', 'Nome da seguradora')->attrs(['class' => '']) !!}
                </div>
                <div class="col-md-4 mt-3">
                    {!! Form::tel('seguradora_cnpj', 'CNPJ da seguradora')->attrs(['class' => 'cpf_cnpj']) !!}
                </div>
                <div class="col-md-4 mt-3">
                    {!! Form::tel('numero_apolice', 'Número da apólice')->attrs(['class' => '']) !!}
                </div>
                <div class="col-md-4 mt-3">
                    {!! Form::tel('numero_averbacao', 'Número da averbação')->attrs(['class' => '']) !!}
                </div>
            </div>
        </div>
        <div class="card col-8">
            <div class="row m-3">
                <div class="col-12 row">
                    <h6>Município(s) de carregamento</h6>
                    <div class="row">
                        <table class="table mb-0 table-striped table-dynamic">
                            <thead>
                                <tr>
                                    <th>Cidade</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody id="body" class="datatable-body">
                                @if (isset($item) && sizeof($item->municipiosCarregamento) > 0)
                                @foreach($item->municipiosCarregamento as $mun)
                                <tr class="dynamic-form">
                                    <td class="col-11">
                                        {!! Form::select('municipiosCarregamento[]', '', [null => 'Selecione'] + $cidades->pluck('info', 'id')->all())
                                        ->attrs(['class' => 'select2'])->required()->value($mun->cidade_id) !!}
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
                                    <td class="col-11">
                                        {!! Form::select('municipiosCarregamento[]', '', [null => 'Selecione'] + $cidades->pluck('info', 'id')->all())
                                        ->attrs(['class' => 'select2 class-municipio class-required'])->required() !!}
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
                            <br>
                            <button type="button" class="btn btn-success btn-add-tr">
                                Adicionar
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="card col-4">
            <div class="row">
                <div class="col-12 m-3 row">
                    <h6>Percurso</h6>
                    <div class="row">
                        <table class="table mb-0 table-striped table-dynamic">
                            <thead>
                                <tr>
                                    <th>UF</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody class="datatable-body" id="tbody">
                                @if (isset($item) && sizeof($item->percurso) > 0)
                                @foreach($item->percurso as $cuf)
                                <tr class="dynamic-form">
                                    <td class="col-10">
                                        {!! Form::select('uf[]', '', App\Models\Mdfe::cUF())
                                        ->attrs(['class' => 'select2'])->value($cuf->uf) !!}
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
                                    <td class="col-8">
                                        {!! Form::select('uf[]', '', ['' => 'Selecione']+App\Models\Mdfe::cUF())
                                        ->attrs(['class' => 'select2']) !!}
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
                    <div class="row mt-3">
                        <div class="col-12">
                            <button type="button" class="btn btn-success btn-add-tr">
                                Adicionar
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    {{-- informações transporte --}}
    <div class="div-transporte d-none">
        <div class="card">
            <div class="row m-3">
                <h6>CIOT (opcional)</h6>
                <div class="table-responsive mt-2">
                    <table class="table table-dynamic">
                        <thead>
                            <tr>
                                <th>Código CIOT</th>
                                <th>CPF / CNPJ</th>
                                <th>Ação</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if(isset($item) && sizeof($item->ciots) > 0)
                            @foreach($item->ciots as $ciot)
                            <tr class="dynamic-form">
                                <td>
                                    <input type="tel" class="form-control codigo_ciot" name="codigo_ciot[]" value="{{$ciot->codigo}}">
                                </td>
                                <td>
                                    <input type="tel" class="form-control cpf_cnpj" name="cpf_cnpj[]" value="{{$ciot->cpf_cnpj}}">
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
                                    <input type="tel" class="form-control codigo_ciot" name="codigo_ciot[]">
                                </td>
                                <td>
                                    <input type="tel" class="form-control cpf_cnpj" name="cpf_cnpj[]">
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
                            Adicionar
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <div class="card">
            <div class="row m-3">
                <h6>Vale pedágio (opcional)</h6>
                <div class="table-responsive mt-4">
                    <table class="table table-dynamic">
                        <thead>
                            <tr>
                                <th>CNPJ</th>
                                <th>CPF / CNPJ Pagador</th>
                                <th>Número da compra</th>
                                <th>Valor</th>
                                <th>Ação</th>

                            </tr>
                        </thead>
                        <tbody>
                            @if(isset($item) && sizeof($item->valesPedagio) > 0)
                            @foreach($item->valesPedagio as $vale)
                            <tr class="dynamic-form">
                                <td>
                                    <input type="tel" class="form-control cnpj_fornecedor cpf_cnpj" name="cnpj_fornecedor[]" value="{{$vale->cnpj_fornecedor}}">
                                </td>
                                <td>
                                    <input type="tel" class="form-control cnpj_fornecedor_pagador" name="cnpj_fornecedor_pagador[]" value="{{$vale->cnpj_fornecedor_pagador}}">
                                </td>
                                <td>
                                    <input type="tel" class="form-control numero_compra" name="numero_compra[]" value="{{$vale->numero_compra}}">
                                </td>
                                <td>
                                    <input type="tel" class="form-control valor" name="valor_pedagio[]" value="{{ __moeda($vale->valor)}}">
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
                                    <input type="tel" class="form-control cnpj_fornecedor cpf_cnpj" name="cnpj_fornecedor[]">
                                </td>
                                <td>
                                    <input type="tel" class="form-control cnpj_fornecedor_pagador cpf_cnpj" name="cnpj_fornecedor_pagador[]">
                                </td>
                                <td>
                                    <input type="tel" class="form-control numero_compra" name="numero_compra[]">
                                </td>
                                <td>
                                    <input type="tel" class="form-control valor moeda" name="valor_pedagio[]">
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
                            Adicionar
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <div class="card">
            <div class="row m-3">
                <h6>Condutor</h6>
                <div class="col-md-4 mt-3">
                    {!! Form::text('condutor_nome', 'Nome')->attrs(['class' => 'class-condutor class-required'])->required() !!}
                </div>
                <div class="col-md-3 mt-3">
                    {!! Form::tel('condutor_cpf', 'CPF')->attrs(['class' => 'cpf class-condutor class-required'])->required() !!}
                </div>
            </div>
        </div>
    </div>
    {{-- informações de descarregamento --}}
    <div class="div-descarregamento d-none">
        <div class="form-descarregamento">
            <div class="card">
                <div class="row m-3">
                    <h6>Informações da unidade de uransporte / Documentos fiscais / Lacres</h6>
                    <div class="col-md-4 mt-3">
                        {!! Form::select(
                        'tp_unid_transp',
                        'Tipo unidade de transporte', ['' => 'Selecione...'] +
                        App\Models\Mdfe::tiposUnidadeTransporte(),
                        )->attrs(['class' => 'select2']) !!}
                    </div>
                    <div class="col-md-4 mt-3">
                        {!! Form::tel('id_unid_transp', 'ID da Unidade de transporte (placa)')->attrs(['class' => 'placa']) !!}
                    </div>
                    <div class="col-md-4 mt-3">
                        {!! Form::tel('quantidade_rateio', 'Quantidade de rateio (transporte)')->attrs(['class' => 'moeda']) !!}
                    </div>
                    <div class="col-md-4 mt-3">
                        {!! Form::tel('id_unidade_carga', 'ID unidade da carga') !!}
                    </div>
                    <div class="col-md-4 mt-3">
                        {!! Form::tel('quantidade_rateio_carga', 'Quantidade de rateio (unidade carga)')->attrs(['data-mask' => '000,00']) !!}
                    </div>
                </div>
            </div>
            <div class="card">
                <div class="row m-3">
                    <h6>NFe referência</h6>
                    <div class="col-md-5 mt-3">
                        {!! Form::tel('chave_nfe', 'NFe referência')->attrs(['class' => 'ignore chave_nfe2']) !!}
                    </div>
                    <div class="col-md-5 mt-3">
                        {!! Form::tel('seg_cod_nfe', 'Segundo código de barra NFe (contingência)')->attrs(['class' => 'ignore']) !!}
                    </div>
                </div>
            </div>
            <div class="card">
                <div class="row m-3">
                    <h6>CTe referência</h6>
                    <div class="col-md-5 mt-3">
                        {!! Form::tel('chave_cte', 'CTe referência')->attrs(['class' => 'ignore chave_nfe2']) !!}
                    </div>
                    <div class="col-md-5 mt-3">
                        {!! Form::tel('seg_cod_cte', 'Segundo código de barra CTe (contingência)')->attrs(['class' => 'ignore']) !!}
                    </div>
                </div>
            </div>
            <div class="row m-auto">
                <div class="card col-6">
                    <div class="row m-3">
                        <h6>Lacres de transporte</h6>
                        <div class="table-responsive mt-2">
                            <table class="table table-striped table-dynamic table-lacres">
                                <thead>
                                    <tr>
                                        <th></th>
                                        <th style="width: 70%">Número lacre</th>
                                        <th>Ação</th>
                                    </tr>
                                </thead>
                                <tbody id="body" class="datatable-body">
                                    <tr class="dynamic-form">
                                        <td></td>
                                        <td class="col-md-5">
                                            {!! Form::tel('numero_transporte[]', '')->attrs(['class' => 'numero_transporte input_lacres']) !!}
                                        </td>
                                        <td>
                                            <br>
                                            <button class="btn btn-danger btn-sm btn-remove-tr">
                                                <i class="bx bx-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="row">
                            <div class="col-12">
                                <button type="button" class="btn btn-success btn-numero_transporte btn-add-tr">
                                    Adicionar
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card col-6">
                    <div class="row m-3">
                        <h6>Lacres da unidade da carga</h6>
                        <div class="table-responsive mt-2">
                            <table class="table table-striped table-dynamic table-lacres-carga">
                                <thead>
                                    <tr>
                                        <th></th>
                                        <th style="width: 70%">Número lacre</th>
                                        <th>Ação</th>
                                    </tr>
                                </thead>
                                <tbody id="body" class="datatable-body">
                                    <tr class="dynamic-form">
                                        <td></td>
                                        <td class="col-md-5">
                                            {!! Form::tel('numero_carga[]', '')->attrs(['class' => 'numero_carga input_lacres']) !!}
                                        </td>
                                        <td>
                                            <br>
                                            <button class="btn btn-danger btn-sm btn-remove-tr">
                                                <i class="bx bx-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
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
                    </div>
                </div>
            </div>
            <div class="card">
                <div class="row m-3">
                    <h6>Município de descarregamento</h6>
                    <div class="col-md-6 mt-3">
                        {!! Form::select('municipio_descarregamento', 'Município', ['' => 'Selecione...'] + $cidades->pluck('info', 'id')->all())->attrs([
                        'class' => 'select2',
                        ]) !!}
                    </div>
                </div>
            </div>
        </div>
        <div class="card">
            <div class="row m-3">
                <h4>Dados do descarregamento</h4>
                <div class="col-md-12 mt-3">
                    <button type="button" class="btn btn-info btn_info_desc">Adicionar informações do
                        descarregamento</button>
                </div>
            </div>
            <div class="table-responsive class-descarregamento mt-4">
                <table class="table mb-0 table-striped table-descarregamento">
                    <thead>
                        <tr>
                            <th>Tipo transporte</th>
                            <th>Id unid transporte</th>
                            <th>Quant rateio</th>
                            <th>Quant rateio carga</th>
                            <th>NFe referência</th>
                            <th>CTe referência</th>
                            <th>Mun descarrega</th>
                            <th>Lacres de transp</th>
                            <th>Lacres unid carga</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody class="" id="">
                        @isset($item)
                        @foreach ($item->infoDescarga as $i)
                        <tr>
                            <td>
                                <input readonly type="sel" name="tp_und_transp_row[]" class="form-control" value="{{ $i->tp_unid_transp }}">
                            </td>
                            <td>
                                <input readonly type="text" name="id_und_transp_row[]" class="form-control" value="{{ $i->id_unid_transp }}">
                            </td>
                            <td>
                                <input readonly type="tel" name="quantidade_rateio_row[]" class="form-control" value="{{ $i->quantidade_rateio }}">
                            </td>
                            <td>
                                <input readonly type="tel" name="quantidade_rateio_carga_row[]" class="form-control" value="{{ $i->unidadeCarga->quantidade_rateio }}">
                            </td>
                            <td>
                                <input readonly type="tel" name="chave_nfe_row[]" class="form-control" value="{{ isset($i->nfe->chave) ? $i->nfe->chave : '' }}">
                            </td>
                            <td>
                                <input readonly type="tel" name="chave_cte_row[]" class="form-control" value="{{ isset($i->cte->chave) ? $i->cte->chave : '' }}">
                            </td>
                            <td>
                                <input readonly type="tel" name="municipio_descarregamento" class="form-control" value="{{ $i->cidade->nome }}">
                                <input readonly type="hidden" name="municipio_descarregamento_row[]" class="form-control" value="{{ $i->cidade->id }}">
                            </td>
                            <td>
                                <input readonly type="tel" name="lacres_transporte_row[]" class="form-control" value="{{ json_encode($i->lacresTransp->pluck('numero')->toArray()) }}">
                            </td>
                            <td>
                                <input readonly type="tel" name="lacres_unidade_row[]" class="form-control" value="{{ json_encode($i->lacresUnidCarga->pluck('numero')->toArray()) }}">
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
    </div>
    <hr>
    <div class="row mt-4 rodape">
        <div class="col-md-6">
            {!! Form::text('info_complementar', 'Informação complementar (opcional)') !!}
        </div>
        <div class="col-md-6">
            {!! Form::text('info_adicional_fisco', 'Informação fiscal (opcional)') !!}
        </div>
        <div class="col-12 alerts mt-4">
        </div>
    </div>

    <div class="mt-4">
        <button type="submit" disabled class="btn btn-primary btn-salvarMdfe px-5">Salvar</button>
    </div>
</div>

@section('js')
<script src="/js/mdfe.js"></script>
@endsection
