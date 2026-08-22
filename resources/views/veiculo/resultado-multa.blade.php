@extends('default.layout', ['title' => 'Resultado das Multas'])

@section('content')
<div class="page-content container mt-5">
    <h2>Resultado da Consulta - Placa: {{ $data['placa'] ?? '-' }}</h2>



    {{-- Informações da API --}}
    <div class="card mb-3">
        <div class="card-header">Informações da API</div>
        <div class="card-body">
            <p><strong>Saldo:</strong> {{ $balance }}</p>
            <p><strong>Mensagem:</strong> {{ $mensagem }}</p>
            <p><strong>Homolog:</strong> {{ $homolog ? 'Sim' : 'Não' }}</p>
            <p><strong>Status Retorno:</strong> {{ $data['status_retorno']['descricao'] ?? '-' }}</p>
        </div>
    </div>

    {{-- Ocorrências --}}
    <div class="card mb-3">
        <div class="card-header">Ocorrências ({{ $data['quantidade_ocorrencias'] ?? 0 }})</div>
        <div class="card-body">
            @if(!empty($data['ocorrencias']))
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Descrição</th>
                            <th>Vencimento</th>
                            <th>Valor (R$)</th>
                            <th>Status</th>
                            <th>Boleto / PDF</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($data['ocorrencias'] as $ocorrencia)
                            <tr>
                                <td>{{ $ocorrencia['descricao'] ?? '-' }}</td>
                                <td>{{ $ocorrencia['data_vencimento'] ?? '-' }}</td>
                                <td>{{ $ocorrencia['total'] ?? '0,00' }}</td>
                                <td>{{ $ocorrencia['status_pgto'] ?? '-' }}</td>
                                <td>
                                    @if(!empty($data['pdf']))
                                        <a href="{{ $data['pdf'] }}" target="_blank" class="btn btn-sm btn-primary">Abrir PDF</a>
                                    @else
                                        -
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <p>Nenhuma ocorrência encontrada.</p>
            @endif
        </div>
    </div>

    {{-- Botão Gerar PDF --}}
    <form action="{{ route('veiculo.multa.pdf') }}" method="POST">
        @csrf
        <input type="hidden" name="user" value="{{ htmlentities(json_encode($user)) }}">
        <input type="hidden" name="data" value="{{ htmlentities(json_encode($data)) }}">
        <input type="hidden" name="mensagem" value="{{ $mensagem }}">
        <input type="hidden" name="balance" value="{{ $balance }}">
        <input type="hidden" name="homolog" value="{{ $homolog }}">
        <button type="submit" class="btn btn-success">Gerar PDF</button>
    </form>

    <a href="{{ route('veiculo.multa.index') }}" class="btn btn-secondary mt-3">Nova Consulta</a>
</div>
@endsection
