@extends('default.layout', ['title' => 'Agendamentos'])
@section('content')

<div class="page-content">
    <div class="card ">
        <div class="card-body p-4">
            <div class="page-breadcrumb d-sm-flex align-items-center mb-3">
                <div>
                    <h4>Agendamento: <strong class="" style="color:rgb(72, 117, 185)">{{ $item->id }}</strong></h4>
                    <h4>Cliente: <strong style="color:steelblue">{{ $item->cliente->razao_social }}</strong></h4>
                    <h4>Atendente: <strong style="color:steelblue">{{ $item->funcionario->nome }}</strong></h4>
                    <h4>Total: <strong style="color:steelblue">{{ __moeda($item->total) }}</strong></h4>
                </div>
            </div>
            <div class="row">
                <div class="col-6">
                    <div class="card">
                        <h5 class="m-3">Data: <strong>{{ $item->created_at }}</strong></h5>
                        <h5 class="m-3">Início: <strong>{{ $item->inicio }}</strong></h5>
                        <h5 class="m-3">Término: <strong>{{ $item->termino }}</strong></h5>
                    </div>
                </div>
                <div class="col-6">
                    <div class="card">
                        <h5 class="m-3">Serviços:</h5>
                        @foreach ($item->itens as $i)
                        <p class="m-3">{{ $i->servico->nome }} - R$ {{ __moeda($i->servico->valor) }}</p>
                        @endforeach
                    </div>
                </div>
            </div>
            <div>
                <form action="{{ route('agendamentos.destroy', $item->id) }}" method="post" id="form-{{ $item->id }}">
                    @method('delete')
                    @csrf
                    <button class="btn btn-danger px-3">Remover agendamento</button>

                    <a href="{{ route('frenteCaixa.index') }}" class="btn btn-info px-3">Ir para frente do caixa</a>
                    
                    @if($item->status == 0)
                    <a href="{{ route('agendamentos.alterarStatus', $item->id) }}" class="btn btn-warning px-3">Alterar para finalizado</a>
                    @else
                    <button type="button" class="btn btn-success">Finalizado</button>
                    @endif
                </form>

            </div>
        </div>
    </div>
</div>
@endsection
