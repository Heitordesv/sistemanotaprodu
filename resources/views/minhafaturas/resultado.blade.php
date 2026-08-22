@extends('default.layout', ['title' => 'Cidades'])

@section('content')
    <h2>Resultados da Consulta</h2>

    @if (!$contas || $contas->isEmpty())
        <p>Não foram encontradas faturas para esse CPF / CNPJ.</p>
    @else
        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Cliente / Empresa</th>
                    <th>Vencimento</th>
                    <th>Valor</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($contas as $conta)
                    <tr>
                        <td>{{ $conta->id }}</td>
                        <td>
                          {{ optional($conta->cliente)->razao_social ?? optional($conta->empresa)->nome_fantasia }}
                        </td>
                        <td>{{ \Carbon\Carbon::parse($conta->data_vencimento)->format('d/m/Y') }}</td>
                        <td>R$ {{ number_format($conta->valor_integral, 2, ',', '.') }}</td>
                        <td>{{ $conta->status ? 'Recebido' : 'Pendente' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
@endsection
