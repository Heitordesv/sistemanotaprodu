@php
    $valorTotal      = $item->valor_integral ?? 0;
    $valorPagoAtual  = $item->valor_pago ?? 0;
    $valorRestante   = $valorTotal - $valorPagoAtual;
@endphp

<div class="card border-0 shadow-sm rounded-4">

    <div class="card-body p-4">

        <h5 class="fw-bold text-primary mb-4">
            <i class="bx bx-wallet"></i>
            Pagamento da Conta
        </h5>

        {{-- RESUMO --}}
        <div class="row g-3 mb-4">

            <div class="col-md-4">

                <div class="bg-light rounded-4 p-3 h-100">

                    <small class="text-muted d-block">
                        Valor Total
                    </small>

                    <h4 class="fw-bold text-primary mb-0 mt-1">
                        R$ {{ __moeda($valorTotal) }}
                    </h4>

                </div>

            </div>

            <div class="col-md-4">

                <div class="bg-light rounded-4 p-3 h-100">

                    <small class="text-muted d-block">
                        Já Pago
                    </small>

                    <h4 class="fw-bold text-success mb-0 mt-1">
                        R$ {{ __moeda($valorPagoAtual) }}
                    </h4>

                </div>

            </div>

            <div class="col-md-4">

                <div class="bg-light rounded-4 p-3 h-100">

                    <small class="text-muted d-block">
                        Falta Pagar
                    </small>

                    <h4 id="valor_restante"
                        class="fw-bold text-danger mb-0 mt-1">

                        R$ {{ __moeda($valorRestante) }}

                    </h4>

                </div>

            </div>

        </div>

        {{-- FORMULÁRIO --}}
        <form action="{{ route('conta-pagar.payPut', $item->id) }}"
              method="POST"
              enctype="multipart/form-data">

            @csrf
            @method('PUT')

            <div class="row g-4">

                {{-- VALOR --}}
                <div class="col-md-4">

                    <label class="form-label fw-semibold">
                        Valor do pagamento
                    </label>

                    <input type="text"
                           name="valor_pago"
                           id="valor_pagamento"
                           class="form-control form-control-lg moeda rounded-3"
                           placeholder="0,00"
                           required>

                </div>

                {{-- DATA --}}
                <div class="col-md-4">

                    <label class="form-label fw-semibold">
                        Data do pagamento
                    </label>

                    <input type="date"
                           name="data_pagamento"
                           class="form-control form-control-lg rounded-3"
                           required>

                </div>

                {{-- TIPO --}}
                <div class="col-md-4">

                    <label class="form-label fw-semibold">
                        Tipo de pagamento
                    </label>

                    <select name="tipo_pagamento"
                            class="form-select form-select-lg rounded-3"
                            required>

                        <option value="">
                            Selecione
                        </option>

                        @foreach(App\Models\ContaPagar::tiposPagamento() as $key => $value)

                            <option value="{{ $key }}">
                                {{ $value }}
                            </option>

                        @endforeach

                    </select>

                </div>

            </div>

            {{-- RESULTADO --}}
            <div class="alert alert-light border rounded-4 mt-4 mb-0">

                <div class="d-flex justify-content-between align-items-center flex-wrap">

                  <!--  <div>

                        <small class="text-muted">
                            Restará após este pagamento
                        </small>

                        <h4 id="restante_final"
                            class="fw-bold text-primary mb-0">

                            R$ {{ __moeda($valorRestante) }}

                        </h4>
                    </div>-->

                    <div class="text-end">

                        <small class="text-muted">
                            Status da conta
                        </small>

                        <h5 id="status_pagamento"
                            class="fw-bold mb-0 text-secondary">

                            Em aberto

                        </h5>

                    </div>

                </div>

            </div>

            {{-- BOTÃO --}}
            <div class="mt-4">

                <button type="submit"
                        class="btn btn-primary btn-lg px-5 rounded-pill">

                    <i class="bx bx-save"></i>
                    Confirmar Pagamento

                </button>

            </div>

        </form>

    </div>

</div>

<script>

    const valorTotalRestante = {{ $valorRestante }};

    const campoPagamento  = document.getElementById('valor_pagamento');
    const restanteFinal   = document.getElementById('restante_final');
    const statusPagamento = document.getElementById('status_pagamento');

    function moedaParaNumero(valor) {

        if (!valor) return 0;

        valor = valor.replace(/\./g, '');
        valor = valor.replace(',', '.');
        valor = valor.replace(/[^\d.-]/g, '');

        return parseFloat(valor) || 0;
    }

    function formatarMoeda(valor) {

        return valor.toLocaleString('pt-BR', {
            style: 'currency',
            currency: 'BRL'
        });

    }

    campoPagamento.addEventListener('input', function () {

        let pagamento = moedaParaNumero(this.value);

        let restante = valorTotalRestante - pagamento;

        if (restante < 0) {
            restante = 0;
        }

        restanteFinal.innerHTML = formatarMoeda(restante);

        if (restante <= 0 && pagamento > 0) {

            statusPagamento.innerHTML = 'Pago';
            statusPagamento.className = 'fw-bold mb-0 text-success';

        } else if (pagamento > 0) {

            statusPagamento.innerHTML = 'Pagamento Parcial';
            statusPagamento.className = 'fw-bold mb-0 text-warning';

        } else {

            statusPagamento.innerHTML = 'Em aberto';
            statusPagamento.className = 'fw-bold mb-0 text-secondary';

        }

    });

</script>