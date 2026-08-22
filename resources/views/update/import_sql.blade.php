@extends('default.layout',['title' => 'SQL'])
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
    <div class="card ">
        <div class="card-body p-4">
            <div class="page-breadcrumb d-sm-flex align-items-center mb-3">

            </div>
            <div class="col">
                <div class="container @if(env('ANIMACAO')) animate__animated @endif animate__backInLeft">
                    <div class="col-lg-12">
                        <div class="col-lg-12">
                            <p class="text-danger ml-4 mt-4">Atenção com os comandos executados nesta tela, pode afetar todo o banco de dados!</p>
                        </div>
                        {!!Form::open()
                        ->post()
                        ->route('appUpdate.sqlStore')
                        !!}
                        <br>
                        <div class="row">
                            <div class="form-group validated col-lg-4">
                                <div class="">
                                    <span style="width: 100%" class="btn btn-primary btn-file">
                                        Procurar arquivo SQL<input required accept=".sql" name="file" type="file">
                                    </span>
                                    <label class="text-info" id="filename"></label>
                                </div>
                            </div>
                            <div class="form-group validated col-lg-2">
                                <div class="">
                                    <button type="submit" class="btn btn-danger">Executar</button>
                                </div>
                            </div>
                        </div>
                        <br>
                        {!!Form::close()!!}

                        <hr>

                        {!!Form::open()
                        ->post()
                        ->route('appUpdate.run-sql')
                        !!}
                        <h5 class="ml-4">Comando Sql</h5>
                        <div class="col-12 form-group">
                            <textarea name="sql" class="form-control"></textarea>
                            <label class="mt-3">separe os comandos com ;</label>
                        </div>
                        <button class="btn btn-dark float-right px-5 mt-3" type="submit">Executar Sql</button>
                        <br>
                        {!!Form::close()!!}
                        <br><br>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
