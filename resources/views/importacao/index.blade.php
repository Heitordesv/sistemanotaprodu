@extends('default.layout',['title' => 'Importação'])
@section('content')
<style type="text/css">
    input[type="file"] {
        display: none;
    }

    .file-importacao label {
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
<div class="page-content">
    <div class="card ">
        {!! Form::open()
        ->post()
        ->route('vendas.importacao')
        !!}
        <div class="card-body p-4">
            <div class="page-breadcrumb d-sm-flex align-items-center mb-3">
                <h6>Importar XML</h6>
            </div>
            <div class="col-md-6 col-xl-4 col-12">
                <p style="color: crimson">Atenção!</p>
                <p>Zipar os arquivos antes de fazer a importação.</p>
            </div>
            <div class="row file-importacao">
                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                <div class="form-group validated col-sm-10 col-lg-10">
                    <div class="col-5">
                        {!! Form::file('file', 'Procurar Arquivo') !!}
                    </div>
                </div>
                <br>
                <div class="col-md-4 mt-4">
                    <button type="submit" class="btn btn-info px-5" href="">
                        <i class="bx bx-plus"></i>Importar XML</button>
                </div>
            </div>
        </div>
        {!!Form::close()!!}
    </div>
</div>

@endsection
