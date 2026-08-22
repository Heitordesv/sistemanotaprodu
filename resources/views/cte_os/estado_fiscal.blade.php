@extends('default.layout',['title' => 'Detalhes CTe Os'])
@section('content')
@section('css')
<style type="text/css">
    input[type="file"] {
        display: none;
    }

    .file-certificado label {
        padding: 10px 10px;
        width: 100%;
        background-color: #8833FF;
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
<div class="page-content">
    <div class="card ">
        <div class="card-body p-4">
            <div class="page-breadcrumb d-sm-flex align-items-center mt-3">
                <div class="ms-auto">
                    <a href="{{ route('cteOs.index')}}" type="button" class="btn btn-light btn-sm">
                        <i class="bx bx-arrow-back"></i> Voltar
                    </a>
                </div>
            </div>
            <div class="page-breadcrumb">
                <div class="row m-3">
                    <h3>CTe Os código: <strong class="text-info">{{ $item->id }}</strong></h3>
                    <hr>
                    <h5> Natureza de operação: <strong class="text-info">{{ $item->natureza->natureza }}</strong></h5>
                    <h5> Data de registro: <strong class="text-info">{{ __data_pt($item->created_at, 0) }}</strong></h5>
                    <h5> Valor de transporte: <strong class="text-info">R$ {{ __moeda($item->valor_transporte) }}</strong></h5>
                    <h5> Valor a receber: <strong class="text-info">R$ {{ __moeda($item->valor_receber) }}</strong></h5>
                </div>
            </div>
            {!!Form::open()
            ->post()
            ->route('cteOs.estadoFiscalStore')
            ->multipart()
            !!}
            <input type="hidden" name="cte_id" value="{{$item->id}}">
            <div class="col-3 m-4">
                {!!Form::select('estado_emissao', 'Estado',
                ['novo' => 'Novo', 'rejeitado' => 'Rejeitado', 'cancelado' => 'Cancelado', 'aprovado' => 'Aprovado']
                )
                ->attrs(['class' => 'form-select'])->value(isset($item) ? $item->estado_emissao : $item->estado_emissao)
                !!}
            </div>
            <div class="m-4">
                <div class="col-md-3 mt-5 file-certificado">
                    <p>Upload XML</p>
                    {!! Form::file('xml', 'Procurar Arquivo')->attrs(['accept' => '.xml']) !!}
                    <span class="text-danger" id="filename"></span>
                </div>
            </div>
            <hr>
        </div>
        <div class="m-3">
            <button class="btn btn-primary px-5" type="submit">Salvar</button>
        </div>
        {!!Form::close()!!}
    </div>
</div>
</div>
@endsection
