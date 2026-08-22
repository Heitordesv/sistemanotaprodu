@extends('default.layout',['title' => 'Contas a Receber'])
@section('content')

<div class="page-content">
    <div class="card border-top border-0 border-4 border-primary">
        <div class="card-body">
            <div class="row">
                <h3 class="center-align">Selecione a tela</h3>
                <div class="col-sm-6 col-lg-6 col-md-6 col-xl-6">
                    <div class="progresso" style="display: none">
                        <div class="spinner spinner-track spinner-primary spinner-lg mr-15"></div>
                    </div>
                </div>
            </div>
            <div class="row mt-3" id="itens">
                <div class="col-sm-4 col-lg-4 col-md-12">
                    <a href="{{ route('controleCozinha.controle') }}"><button style="height: 90px; width: 100%; margin-top: 5px;" class="btn btn-info">
                            Todos
                        </button></a>
                </div>
                @foreach($telas as $t)
                <div class="col-sm-4 col-lg-4 col-md-12">
                    <a href="/controleCozinha/controle/{{$t->id}}"><button style="height: 90px; width: 100%; margin-top: 5px;" class="btn btn-info">
                            {{$t->nome}}
                        </button></a>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

@endsection
