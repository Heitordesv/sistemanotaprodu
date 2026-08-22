@extends('default.layout',['title' => 'Upload APK'])
@section('content')
<style type="text/css">
    input[type="file"] {
        display: none;
    }

    .btn-file label {
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
    <div class="card border-top border-0 border-3 border-dark">
        <div class="col-lg-12">
            <br>
            <form class="container" method="post" action="{{ route('pedidos.upload-store') }}" enctype="multipart/form-data">
                <div class="">
                    <div class="card-header">
                        <h5>Upload de APK</h5>
                        @if($rotaDownload != '')
                        <a target="_blank" class="mt-5" href="{{$rotaDownload}}">{{ $rotaDownload }}</a>
                        @endif
                        @if($rotaDownloadGenerico != '')
                        <a target="_blank" class="mt-5" href="{{$rotaDownloadGenerico}}">
                            Clique aqui para fazer o download do App {{env("APP_NAME")}}
                        </a>
                        @endif
                    </div>
                    @csrf
                    <div class="row m-3">
                        <div class="col-xl-8">
                            <div class="kt-section kt-section--first">
                                <div class="kt-section__body">
                                    <div class="row">
                                        <div class="form-group validated col-sm-6 col-lg-6">
                                            <div class="">
                                                <div class="col-md-12 mt-3 btn-file">
                                                    {!! Form::file('file', 'Selecionar arquivo')
                                                    ->attrs(['accept' => '.apk']) !!}
                                                    <span class="text-danger" id="filename"></span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <h5 class="mt-3">Exemplo de conexão</h5>
                                    <h6>Path: <strong>{{ env("APP_URL") }}</strong></h6>
                                    <h6>Usuário: <strong>{{ \App\Models\Usuario::find(session('user_logged')['id'])->login }}</strong></h6>
                                    <h6>Senha: <strong>senha do usuário</strong></h6>
                                    <h6>Chave App: <strong>{{ env("KEY_APP") }}</strong></h6>
                                </div>
                            </div>
                        </div>
                    </div>
                    <hr>
                    <div class="m-3">
                        <button type="submit" class="btn btn-primary px-5">Enviar arquivo</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
