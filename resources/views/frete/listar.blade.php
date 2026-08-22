@extends('default.layout', ['title' => 'Fretes Escolhidos'])

@section('content')
<div class="container my-4">
    <h2>Fretes Escolhidos</h2>

    @if($fretes->count())
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Nome</th>
                    <th>Preço (R$)</th>
                    <th>Prazo (dias)</th>
                    <th>CEP Origem</th>
                    <th>CEP Destino</th>
                    <th>Data</th>
                </tr>
            </thead>
            <tbody>
                @foreach($fretes as $frete)
                    <tr>
                        <td>{{ $frete->name }}</td>
                        <td>{{ number_format($frete->price,2,',','.') }}</td>
                        <td>{{ $frete->delivery_time }}</td>
                        <td>{{ $frete->cep_origem }}</td>
                        <td>{{ $frete->cep_destino }}</td>
                        <td>{{ $frete->created_at->format('d/m/Y H:i') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p>Nenhum frete escolhido ainda.</p>
    @endif
</div>
@endsection
