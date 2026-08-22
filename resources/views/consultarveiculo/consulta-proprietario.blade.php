@extends('default.layout', ['title' => 'Consulta de Proprietário - API Brasil'])

@section('content')
<div class="page-content container mt-5">
    <h2>Consulta de Veículo e Proprietário</h2>

    {{-- Formulário de consulta --}}
    <form action="{{ route('consultarveiculo.proprietario') }}" method="POST" class="mb-4">
        @csrf
        <div class="input-group mb-3">
            <input type="text" name="placa" class="form-control" placeholder="Placa: ABC1234" required>
            <button type="submit" class="btn btn-primary">Consultar</button>
        </div>
    </form>

    {{-- Dados do Veículo --}}
    @isset($veiculo)
        <div class="card mt-4">
            <div class="card-header bg-info text-white">Dados do Veículo</div>
            <div class="card-body p-0">
                <table class="table table-bordered mb-0">
                    <tbody>
                        @foreach($veiculo as $campo => $valor)
                            @if(!is_array($valor))
                                <tr>
                                    <th>{{ ucfirst(str_replace('_',' ',$campo)) }}</th>
                                    <td>{{ $valor ?: '-' }}</td>
                                </tr>
                            @endif
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endisset

    {{-- Dados do Proprietário --}}
    @isset($proprietario)
        <div class="card mt-4">
            <div class="card-header bg-success text-white">Dados do Proprietário</div>
            <div class="card-body p-0">
                <table class="table table-bordered mb-0">
                    <tbody>
                        @foreach($proprietario as $campo => $valor)
                            @if(!is_array($valor))
                                <tr>
                                    <th>{{ ucfirst(str_replace('_',' ',$campo)) }}</th>
                                    <td>{{ $valor ?: '-' }}</td>
                                </tr>
                            @endif
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Botão gerar PDF --}}
<form action="{{ route('consultarveiculo.pdfProprietario') }}" method="POST" target="_blank">
            @csrf
            <input type="hidden" name="placa" value="{{ $placa }}">
            <input type="hidden" name="veiculo" value="{{ json_encode($veiculo) }}">
            <input type="hidden" name="proprietario" value="{{ json_encode($proprietario) }}">
            <button type="submit" class="btn btn-danger">Gerar PDF</button>
        </form>
    @endisset
</div>
@endsection
