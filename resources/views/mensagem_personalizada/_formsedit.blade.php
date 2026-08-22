@extends('default.layout', ['title' => 'Editar Mensagem Personalizada'])

@section('content')
<br>
<div class="row justify-content-center">
    <div class="col-md-11">
        <br><br><br><br>
        <div class="container">
            <h2>Editar Mensagem Personalizada</h2>

            @if($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach($errors->all() as $erro)
                            <li>{{ $erro }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('mensagem_personalizada.update', $mensagem->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="row">
                    <div class="col-md-12">
                        {!! Form::textarea('mensagem', 'Mensagem', $mensagem->mensagem)
                            ->attrs(['class' => 'form-control', 'rows' => 6, 'required']) !!}
                        <small class="text-muted d-block mt-2">
                            Cobrança: {cliente}, {valor}, {vencimento}, {referencia}, {agente}, {conta_id}.<br>
                            <strong>Privacidade:</strong> links não são enviados na cobrança automática. Oriente o cliente a tocar em <strong>Quero pagar</strong> ou responder <strong>QUERO PAGAR</strong>.<br>
                            Ordem de Serviço: {cliente}, {os}, {os_id}, {status}, {descricao}, {valor}.
                        </small>
                    </div>

                    <div class="col-md-4 mt-3">
                        {!! Form::select('status', 'Status', [
                                'ativa' => 'Ativa',
                                'inativa' => 'Inativa',
                            ], $mensagem->status)
                            ->attrs(['class' => 'form-control', 'required']) !!}
                    </div>

                    <div class="col-md-8 mt-3">
                        {!! Form::select('tipo', 'Tipo da Mensagem', [
                                'Aberto' => 'Pedido - Aberto',
                                'Em Andamento' => 'Pedido - Em Andamento',
                                'Saiu para Entrega' => 'Pedido - Saiu para Entrega',
                                'Disponível para Retirada' => 'Pedido - Disponível para Retirada',
                                'Finalizado' => 'Pedido - Finalizado',
                                'Cancelado' => 'Pedido - Cancelado',
                                'Cobranca Antes' => 'Cobrança - Antes do vencimento',
                                'Cobranca Hoje' => 'Cobrança - Vence hoje',
                                'Cobranca Atraso' => 'Cobrança - Em atraso',
                                'OS Pendente' => 'OS - Pendente',
                                'OS Em Andamento' => 'OS - Em andamento',
                                'OS Pronto' => 'OS - Pronto',
                                'OS Finalizado' => 'OS - Finalizado',
                                'OS Reprovado' => 'OS - Reprovado',
                            ], $mensagem->tipo)
                            ->attrs(['class' => 'form-control', 'required']) !!}
                    </div>

                    <div class="col-12 mt-4 text-center">
                        <button type="submit" class="btn btn-success px-5">Salvar Mensagem</button>
                        <a href="{{ route('mensagem_personalizada.index') }}" class="btn btn-secondary">Voltar</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection