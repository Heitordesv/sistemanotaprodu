@extends('default.layout', ['title' => 'Consulta de Veículo - API Brasil'])

@section('content')
<div class="page-content container mt-5">
    <div class="card p-4">
        <h2 class="mb-4">Consulta Veicular / R.F.</h2>
{{-- Botão de pagamento --}}
<a href="https://checkout.mixksolutions.com.br/pg/?item=10" 
   target="_blank" 
   class="btn btn-success mt-3 d-inline-block">
   💳 COBRAR NO PIX
</a>        {{-- Formulário de consulta --}}
        <form action="{{ route('consultarveiculo.consultar') }}" method="POST" class="mb-4">
            @csrf
            <div class="mb-3">
                <label for="placa" class="form-label">Placa:</label>
                <input type="text" name="placa" id="placa" class="form-control" required placeholder="Ex: ABC1234">
            </div>
            <button type="submit" class="btn btn-primary">Consultar</button>
        </form>

        @isset($veiculo)
            <div class="card mt-4">
                <div class="card-header">Dados do Veículo</div>
                <div class="card-body p-3">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Campo</th>
                                <th>Valor</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr><td>Placa</td><td>{{ $veiculo['placa'] ?? '-' }}</td></tr>
                            <tr><td>Situação</td><td>{{ $veiculo['situacao'] ?? '-' }}</td></tr>
                            <tr><td>Ocorrência</td><td>{{ $veiculo['ocorrencia'] ?? '-' }}</td></tr>
                            <tr><td>Renavam</td><td>{{ $veiculo['renavam'] ?? '-' }}</td></tr>
                            <tr><td>Município</td><td>{{ $veiculo['municipio'] ?? '-' }}</td></tr>
                            <tr><td>UF</td><td>{{ $veiculo['uf'] ?? '-' }}</td></tr>
                            <tr><td>Chassi</td><td>{{ $veiculo['chassi'] ?? '-' }}</td></tr>
                            <tr><td>Marca</td><td>{{ $veiculo['marca'] ?? '-' }}</td></tr>
                            <tr><td>Tipo de Veículo</td><td>{{ $veiculo['tipo_veiculo'] ?? '-' }}</td></tr>
                            <tr><td>Ano Fabricação</td><td>{{ $veiculo['ano_fabricacao'] ?? '-' }}</td></tr>
                            <tr><td>Ano Modelo</td><td>{{ $veiculo['ano_modelo'] ?? '-' }}</td></tr>
                            <tr><td>Cor</td><td>{{ $veiculo['cor'] ?? '-' }}</td></tr>
                            <tr><td>Combustível</td><td>{{ $veiculo['combustivel'] ?? '-' }}</td></tr>
                            <tr><td>Capacidade Passageiros</td><td>{{ $veiculo['capacidade_passageiros'] ?? '-' }}</td></tr>
                            <tr><td>Capacidade Carga</td><td>{{ $veiculo['capacidade_carga'] ?? '-' }}</td></tr>
                            <tr><td>PBT</td><td>{{ $veiculo['pbt'] ?? '-' }}</td></tr>
                            <tr><td>Restrição</td><td>{{ $veiculo['restricao'] ?? '-' }}</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Botão gerar PDF --}}
            <form action="{{ route('consultarveiculo.pdf') }}" method="POST" target="_blank" class="mt-3">
                @csrf
                <input type="hidden" name="placa" value="{{ $placa }}">
                <input type="hidden" name="veiculo" value="{{ json_encode($veiculo) }}">
                <button type="submit" class="btn btn-danger">Gerar PDF</button>
            </form>
        @endisset
    </div>
</div>
@endsection
