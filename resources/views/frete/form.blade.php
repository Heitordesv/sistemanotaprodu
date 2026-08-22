@extends('default.layout', ['title' => 'Calcular Frete'])

@section('content')
<div class="page-content">
    <div class="card p-4">
        <div class="container">
            <h2 class="mb-4">Calcular Frete</h2>

            {{-- Mensagem de sucesso --}}
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            {{-- Formulário de cálculo --}}
            <form method="POST" action="{{ route('frete.calcular') }}">
                @csrf

                <div class="row g-3">
                    <div class="col-md-6">
                        <label>CEP Origem</label>
                        <input type="text" name="cep_origem" class="form-control cep-mask"
                               value="{{ old('cep_origem') }}" placeholder="12345-678" required>
                    </div>

                    <div class="col-md-6">
                        <label>CEP Destino</label>
                        <input type="text" name="cep_destino" class="form-control cep-mask"
                               value="{{ old('cep_destino') }}" placeholder="12345-678" required>
                    </div>

                    <div class="col-md-4">
                        <label>Peso (kg)</label>
                        <input type="number" name="peso" step="0.1" class="form-control"
                               value="{{ old('peso') }}" required>
                    </div>

                    <div class="col-md-4">
                        <label>Largura (cm)</label>
                        <input type="number" name="largura" class="form-control"
                               value="{{ old('largura') }}" required>
                    </div>

                    <div class="col-md-4">
                        <label>Altura (cm)</label>
                        <input type="number" name="altura" class="form-control"
                               value="{{ old('altura') }}" required>
                    </div>

                    <div class="col-md-6">
                        <label>Comprimento (cm)</label>
                        <input type="number" name="comprimento" class="form-control"
                               value="{{ old('comprimento') }}" required>
                    </div>

                    <div class="col-md-6">
                        <label>Valor Declarado (R$)</label>
                        <input type="number" name="valor" step="0.01" class="form-control"
                               value="{{ old('valor') }}" required>
                    </div>
                </div>

                <div class="mt-4">
                    <button type="submit" class="btn btn-primary w-100">Calcular Frete</button>
                </div>
            </form>
        </div>
<br>
<br>
    <h2 class="mb-3">Fretes Escolhidos</h2>

    @if($fretes->count())
        <div class="table-responsive">
            <table class="table table-striped table-bordered align-middle">
                <thead class="table-dark">
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
                            <td>{{ number_format($frete->price, 2, ',', '.') }}</td>
                            <td>{{ $frete->delivery_time }}</td>
                            <td>{{ $frete->cep_origem }}</td>
                            <td>{{ $frete->cep_destino }}</td>
                            <td>{{ $frete->created_at->format('d/m/Y H:i') }}</td>
  <td>
                <a href="https://checkout.mixksolutions.com.br/correio/?item={{ $frete->id }}" 
                   class="btn btn-success btn-sm" target="_blank">
                   Pagar
                </a>                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <p class="text-muted">Nenhum frete escolhido ainda.</p>
    @endif
</div>
@endsection

@section('js')
<script>
    document.querySelectorAll('.cep-mask').forEach(input => {
        input.addEventListener('input', () => {
            let value = input.value.replace(/\D/g, '');
            if (value.length > 5) {
                value = value.slice(0, 5) + '-' + value.slice(5, 8);
            }
            input.value = value;
        });
    });
</script>
@endsection
