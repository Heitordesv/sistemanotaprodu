@section('css')
<style type="text/css">
    input[type="file"] {
        display: none;
    }

    .file-certificado label {
        padding: 10px 10px;
        width: 100%;
        background-color: #1C1F23;
        color: #FFF;
        text-transform: uppercase;
        text-align: center;
        display: block;
        margin-top: 15px;
        cursor: pointer;
        border-radius: 5px;
    }

</style>
@endsection
<div class="row g-3">
    {{-- <div class="col-md-3 file-certificado">
        {!! Form::file('xml', 'Importar XML NFe')->attrs(['accept' => '.xml']) !!}
    </div> --}}
</div>
<br>
<input type="hidden" id="clientes" value="{{json_encode($clientes)}}" name="">

<div class="row g-3">
    <div class="col-md-6">
        {!! Form::select('natureza_id', 'Natureza de operação', ['' => 'Selecione'] + $naturezas->pluck('natureza', 'id')->all())->attrs([
        'class' => 'select2 class-required',
        ])->required() !!}
    </div>

    <div class="col-md-4">
        {!! Form::select('cst', 'CST', App\Models\CteOs::getCsts())->attrs(['class' => 'select2']) !!}
    </div>
    <div class="col-md-2">
        {!! Form::text('perc_icms', '%ICMS')->required()->attrs(['class' => 'perc class-required']) !!}
    </div>
    <div class="col-md-6">
        {!! Form::select(
        'remetente_id',
        'Emitente',
        ['' => 'Selecione'] + $clientes->pluck('razao_social', 'id')->all(),
        )->attrs(['class' => 'select2 class-required'])->required()
        ->value(isset($item) ? $item->remetente_id : null) !!}
        <div class="card mt-3 div-remetente d-none">
            <div class="m-3">
                <h5 style="color: rgb(13, 197, 13)" class="text-center">EMITENTE SELECIONADO</h5>
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
        'Tomador',
        ['' => 'Selecione'] + $clientes->pluck('razao_social', 'id')->all(),
        )->attrs(['class' => 'select2 class-required'])->required()
        ->value(isset($item) ? $item->destinatario_id : null) !!}
        <div class="card mt-3 div-destinatario d-none">
            <div class="m-3">
                <h5 style="color: rgb(13, 197, 13)" class="text-center">TOMADOR SELECIONADO</h5>
                <hr>
                <H6>Razão Social: <strong id="razao_social_destinatario"></strong></H6>
                <H6>CNPJ: <strong id="cnpj_destinatario"></strong></H6>
                <H6>Cidade: <strong id="cidade_destinatario"></strong></H6>
            </div>
        </div>
    </div>
    <hr class="mt-5">
    <div class="row g-3">
        <h3>Informações da Carga</h3>
        <div class="col-md-3">
            {!! Form::select('veiculo_id', 'Veiculo', ['' => 'Selecione'] + $veiculos->pluck('placa', 'id')
            ->all())->attrs(['class' => 'select2'])
            ->required() !!}
        </div>
        <div class="col-md-2">
            {!! Form::select('tomador', 'Tomador', App\Models\CteOs::tiposTomador())->attrs(['class' => 'select2'])
            ->required() !!}
        </div>
        <div class="col-md-2">
            {!! Form::tel('valor_transporte', 'Valor carga')->attrs(['class' => 'moeda'])
            ->required() !!}
        </div>
        <div class="col-md-2">
            {!! Form::tel('valor_receber', 'Valor a receber')->attrs(['class' => 'moeda'])
            ->required() !!}
        </div>
        <div class="col-md-3">
            {!! Form::select('modal', 'Modelo de transporte',
            App\Models\CteOs::modals())->attrs(['class' => 'select2'])->required() !!}
        </div>
    </div>
    <hr class="mt-5">
    <h4>Informação de entrega</h4>
    <h6 class="mt-2" style="color: cornflowerblue">Endereço do tomador</h6>
    <div class="col-md-4">
        {!! Form::select('municipio_envio', 'Município de envio', ['' => 'Selecione'] + $cidades->pluck('nome', 'id')->all())->attrs([
        'class' => 'select2',
        ])->required() !!}
    </div>
    <div class="col-md-4">
        {!! Form::select('municipio_inicio', 'Município de início', ['' => 'Selecione'] + $cidades->pluck('nome', 'id')->all())->attrs([
        'class' => 'select2',
        ])->required() !!}
    </div>
    <div class="col-md-4">
        {!! Form::select('municipio_fim', 'Município final', ['' => 'Selecione'] + $cidades->pluck('nome', 'id')->all())->attrs([
        'class' => 'select2',
        ])->required() !!}
    </div>

    <div class="col-md-6">
        {!! Form::text('descricao_servico', 'Descrição do serviço')->required() !!}
    </div>

    <div class="col-md-2">
        {!! Form::text('quantidade_carga', 'Qtd de carga')->attrs(['class' => 'qtd'])->required() !!}
    </div>

    <div class="col-md-2">
        {!! Form::date('data_viagem', 'Data de viagem')->required() !!}
    </div>
    <div class="col-md-2">
        {!! Form::text('horario_viagem', 'Horário de viagem')->attrs(['data-mask' => '00:00'])->required() !!}
    </div>
    <hr class="mt-5">
    <div class="col-md-12">
        {!! Form::text('observacao', 'Informação adicional')->required() !!}
    </div>

    <div class="col-12 mt-5">
        <button type="submit" class="btn btn-primary px-5">Salvar</button>
    </div>
</div>
@section('js')
<script src="/js/cte.js"></script>
@endsection
