@extends('default.layout', ['title' => 'Calcular Frete'])

@section('content')
<div class="page-content py-4">
    <div class="container">
        <h2 class="mb-4 text-center">Resultado do Frete</h2>

        @if(!empty($data))
            <div class="row g-4">
                @foreach($data as $frete)
                    @php $precoComAumento = $frete['price'] * 1.10; @endphp
                    <div class="col-md-4">
                        <div class="card shadow-sm h-100">
                            <div class="card-body d-flex flex-column justify-content-between">
                                <div>
                                    <h5 class="card-title">{{ $frete['name'] }}</h5>
                                    <p class="card-text mb-1"><strong>Preço:</strong> R$ {{ number_format($precoComAumento, 2, ',', '.') }}</p>
                                    <p class="card-text mb-0"><strong>Prazo:</strong> {{ $frete['delivery_time'] }} dias</p>
                                </div>

                                <button type="button" class="btn btn-success mt-3 w-100" data-bs-toggle="modal" data-bs-target="#modalFrete{{ $loop->index }}">
                                    Escolher
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Modal -->
                    <div class="modal fade" id="modalFrete{{ $loop->index }}" tabindex="-1" aria-labelledby="modalLabel{{ $loop->index }}" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <form action="{{ route('frete.escolher') }}" method="POST">
                                    @csrf
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="modalLabel{{ $loop->index }}">Confirme o Frete</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <p><strong>Serviço:</strong> {{ $frete['name'] }}</p>
                                        <p><strong>Preço:</strong> R$ {{ number_format($precoComAumento, 2, ',', '.') }}</p>
                                        <p><strong>Prazo:</strong> {{ $frete['delivery_time'] }} dias</p>

                                        <input type="hidden" name="name" value="{{ $frete['name'] }}">
                                        <input type="hidden" name="price" value="{{ $precoComAumento }}">
                                        <input type="hidden" name="delivery_time" value="{{ $frete['delivery_time'] }}">
                                        <input type="hidden" name="cep_origem" value="{{ $request->cep_origem }}">
                                        <input type="hidden" name="cep_destino" value="{{ $request->cep_destino }}">
                                        <input type="hidden" name="empresa_id" value="{{ session('user_logged')['empresa'] }}">
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                        <button type="submit" class="btn btn-success">Confirmar</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <p class="text-muted text-center mt-4">Nenhum resultado retornado.</p>
        @endif
    </div>
</div>
@endsection

@section('js')
<script>
    // Máscara de CEP
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
