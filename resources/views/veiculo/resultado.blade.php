@extends('default.layout', ['title' => 'Resultado da Consulta - API Brasil'])

@section('content')
<div class="page-content container mt-5">
    <h2>Resultado da Consulta - Veículo {{ $placa }}</h2>

    <p><strong>Mensagem:</strong> {{ $mensagem }}</p>
    <p><strong>Saldo restante:</strong> {{ $balance }}</p>

    @if(!empty($veiculo))
        {{-- Dados do Veículo --}}
        <div class="section-title" style="background:#007bff;color:white;padding:5px;margin-top:20px;">Dados do Veículo</div>
        <table class="table table-bordered mt-2">
            <tr>
                <th>Placa</th>
                <td>{{ $veiculo['placa'] ?? '' }}</td>
                <th>Renavam</th>
                <td>{{ $veiculo['renavam'] ?? '' }}</td>
            </tr>
            <tr>
                <th>Marca / Modelo</th>
                <td>{{ $veiculo['modelo_marca'] ?? '' }}</td>
                <th>Cor</th>
                <td>{{ $veiculo['cor'] ?? '' }}</td>
            </tr>
            <tr>
                <th>Ano Fabricação</th>
                <td>{{ $veiculo['ano_fabricacao'] ?? '' }}</td>
                <th>Ano Modelo</th>
                <td>{{ $veiculo['ano_modelo'] ?? '' }}</td>
            </tr>
            <tr>
                <th>Combustível</th>
                <td>{{ $veiculo['combustivel'] ?? '' }}</td>
                <th>Potência</th>
                <td>{{ $veiculo['potencia'] ?? '' }}</td>
            </tr>
            <tr>
                <th>Tipo de Veículo</th>
                <td>{{ $veiculo['tipo_veiculo'] ?? '' }}</td>
                <th>Espécie</th>
                <td>{{ $veiculo['especie'] ?? '' }}</td>
            </tr>
        </table>

        {{-- Dados do Proprietário / Cliente --}}
        @if(isset($veiculo['cliente']))
        <div class="section-title" style="background:#28a745;color:white;padding:5px;margin-top:20px;">Proprietário / Cliente</div>
        <table class="table table-bordered mt-2">
            <tr>
                <th>Nome</th>
                <td>{{ $veiculo['cliente']['nome'] ?? '' }}</td>
                <th>CPF / CNPJ</th>
                <td>{{ $veiculo['cliente']['documento'] ?? '' }}</td>
            </tr>
            <tr>
                <th>Telefone</th>
                <td>{{ implode(', ', $veiculo['cliente']['telefones'] ?? []) }}</td>
                <th>E-mails</th>
                <td>{{ implode(', ', $veiculo['cliente']['emails'] ?? []) }}</td>
            </tr>
            <tr>
                <th>Endereço</th>
                <td colspan="3">
                    @if(isset($veiculo['cliente']['enderecos'][0]))
                        {{ $veiculo['cliente']['enderecos'][0]['rua'] ?? '' }},
                        {{ $veiculo['cliente']['enderecos'][0]['numero'] ?? '' }},
                        {{ $veiculo['cliente']['enderecos'][0]['bairro'] ?? '' }},
                        {{ $veiculo['cliente']['enderecos'][0]['cidade'] ?? '' }} -
                        {{ $veiculo['cliente']['enderecos'][0]['estado'] ?? '' }},
                        CEP: {{ $veiculo['cliente']['enderecos'][0]['cep'] ?? '' }}
                    @endif
                </td>
            </tr>
        </table>
        @endif

        {{-- Botão para gerar PDF --}}
        <form action="{{ route('veiculo.pdf') }}" method="POST">
            @csrf
            <input type="hidden" name="veiculo" value='@json($veiculo)'>
            <input type="hidden" name="placa" value="{{ $placa }}">
            <button type="submit" class="btn btn-success mt-3">Gerar PDF</button>
        </form>
    @else
        <div class="alert alert-warning">Nenhum dado encontrado.</div>
    @endif
</div>
@endsection
