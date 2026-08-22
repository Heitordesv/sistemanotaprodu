@extends('default.layout', ['title' => 'Consulta de Multas - API Brasil'])

@section('content')
<div class="container mt-5">
    <h2>Consulta de Multas e Infrações</h2>

    {{-- Formulário --}}
    <form action="{{ route('consultarveiculo.multas') }}" method="POST" class="mb-4">
        @csrf
        <div class="row">
            <div class="col-md-4 mb-3">
                <label for="placa">Placa:</label>
                <input type="text" name="placa" id="placa" class="form-control" placeholder="Ex: ABC1234" value="{{ old('placa', $placa ?? '') }}" required>
            </div>
        </div>
        <button type="submit" class="btn btn-primary">Consultar</button>
    </form>

    {{-- Mensagens de erro ou status --}}
    @if(!empty($error))
        <div class="alert alert-danger">{{ $error }}</div>
    @endif

    @if(!empty($status))
        <div class="alert alert-info">{{ $status }}</div>
    @endif

    {{-- Tabela de ocorrências --}}
    @if(!empty($ocorrencias) && is_array($ocorrencias))
        <div class="card mt-4">
            <div class="card-header">Ocorrências/Multas</div>
            <div class="card-body">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Descrição</th>
                            <th>Data Vencimento</th>
                            <th>Status Pagamento</th>
                            <th>Valor</th>
                            <th>PDF</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($ocorrencias as $multa)
                        <tr>
                            <td>{{ $multa['descricao'] ?? '-' }}</td>
                            <td>{{ $multa['data_vencimento'] ?? '-' }}</td>
                            <td>{{ $multa['status_pgto'] ?? '-' }}</td>
                            <td>{{ isset($multa['total']) ? 'R$ ' . number_format($multa['total'], 2, ',', '.') : '-' }}</td>
                            <td>
                                @if(isset($pdf) && $pdf)
                                    <a href="{{ $pdf }}" target="_blank" class="btn btn-sm btn-info">PDF</a>
                                @else
                                    <span>-</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>

                {{-- Botão gerar PDF --}}
                <form action="{{ route('consultarveiculo.pdfMultas') }}" method="POST" target="_blank" class="mt-3">
                    @csrf
                    <input type="hidden" name="placa" value="{{ $placa }}">
                    <input type="hidden" name="ocorrencias" value="{{ json_encode($ocorrencias) }}">
                    <input type="hidden" name="pdf" value="{{ $pdf ?? '' }}">
                    <button type="submit" class="btn btn-danger">Gerar PDF</button>
                </form>
            </div>
        </div>
    @endif
</div>
@endsection
