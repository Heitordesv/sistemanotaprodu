@extends('default.layout', ['title' => 'Contas a Receber'])

@section('content')
<div class="page-content">
    <div class="card">
        <div class="card-body p-4">
            <div class="col">
                <h6 class="mb-0 text-uppercase">Meus Planos</h6>
            </div>

            <hr />

@php
    $faturasAbertas = 0;
    $faturasVencidasOuHoje = 0;
    $saldoVencidoOuHoje = 0;
    $faturasPagas = 0;
    $totalValorIntegral = 0;
    $totalValorRecebido = 0;
    $totalValorAPagar = 0;
    $hoje = date('Y-m-d');
    $faturasVencemHoje = [];
@endphp

@foreach($data as $item)
    @php
        $valorAPagar = max(0, $item->valor_integral - $item->valor_recebido);
        $totalValorIntegral += $item->valor_integral;
        $totalValorRecebido += $item->valor_recebido;
        $totalValorAPagar += $valorAPagar;

        if (!$item->status) {
            $faturasAbertas++;

            if (strtotime($item->data_vencimento) <= strtotime($hoje)) {
                $faturasVencidasOuHoje++;
                $saldoVencidoOuHoje += $valorAPagar;
            }

            if ($item->data_vencimento == $hoje) {
                $faturasVencemHoje[] = $item;
            }
        } else {
            $faturasPagas++;
        }
    @endphp
@endforeach

<div class="row mb-4">
    <!-- Card 1 -->
    <div class="col-md-4 d-flex">
        <div class="card border-danger w-100 h-100">
            <div class="card-body text-center d-flex flex-column justify-content-center">
                <h5 class="card-title text-danger">Faturas em aberto (vencidas ou vencem hoje)</h5>
                <p class="mb-1">Total: <strong>{{ $faturasVencidasOuHoje }}</strong> faturas</p>
                <p class="card-text display-6">R${{ __moeda($saldoVencidoOuHoje) }}</p>

                @if(count($faturasVencemHoje) > 0)
                    @foreach($faturasVencemHoje as $fatura)
                        @php
                            $valorPagarHoje = max(0, $fatura->valor_integral - $fatura->valor_recebido);
                            $linkPagamento = "https://checkout.mixksolutions.com.br/recorrencia/?item={$fatura->id}";
                        @endphp
                        <center>
                            <div class="col-md-9 d-flex justify-content-center">
                                <style>
                                    .btn-success-claro {
                                        background-color: #29a71a;
                                        border-color: #6cc07e;
                                        color: #fff;
                                    }

                                    .btn-success-claro:hover {
                                        background-color: #29a71a;
                                        border-color: #5ca366;
                                    }
                                </style>
                                <a href="{{ $linkPagamento }}" class="btn btn-success-claro btn-sm" target="_blank">Pagar Agora</a>
                            </div>
                        </center>
                    @endforeach
                @else
                    <p>Nenhuma fatura vence hoje.</p>
                @endif
            </div>
        </div>
    </div>

    <!-- Card 2 -->
    <div class="col-md-4 d-flex">
        <div class="card border-success w-100 h-100">
            <div class="card-body text-center d-flex flex-column justify-content-center">
                <h5 class="card-title text-success">Pagamentos Recebidos</h5>
                <p class="mb-1">Faturas pagas: <strong>{{ $faturasPagas }}</strong></p>
                <p class="card-text display-6"><strong>R${{ __moeda($totalValorRecebido) }}</strong></p>
            </div>
        </div>
    </div>

    <!-- Card 3 -->
    <div class="col-md-4 d-flex">
        <div class="card border-warning w-100 h-100">
            <div class="card-body text-center d-flex flex-column justify-content-center">
                <h5 class="card-title text-warning">Total a Pagar</h5>
                <p class="card-text display-6">R${{ __moeda($totalValorAPagar) }}</p>
                <p class="mb-0">Faturas em aberto: <strong>{{ $faturasAbertas }}</strong></p>
            </div>
        </div>
    </div>
</div>

<!-- Botões de seleção -->
<div class="mb-3">
    <button class="btn btn-outline-primary btn-sm" onclick="selecionarTodos()">Selecionar Todos</button>
    <button class="btn btn-outline-info btn-sm" onclick="selecionarAleatorios(3)">Selecionar Aleatórios</button>
</div>

{{-- TABELA --}}
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table mb-0 table-striped">
                <thead>
                    <tr>
                        <th><input type="checkbox" id="checkAll"></th>
                        <th>Valor integral</th>
                        <th>Valor recebido</th>
                        <th>Total a pagar</th>
                        <th>Data de cadastro</th>
                        <th>Data de vencimento</th>
                        <th>Data de recebimento</th>
                        <th>Estado</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($data as $item)
                        @php
                            $valorAPagar = max(0, $item->valor_integral - $item->valor_recebido);
                            $linkPagamento = "https://checkout.mixksolutions.com.br/recorrencia/?item={$item->id}";
                        @endphp
                        <tr>
                            <td><input type="checkbox" class="check" value="{{ $item->id }}"></td>
                            <td>{{ __moeda($item->valor_integral) }}</td>
                            <td>{{ __moeda($item->valor_recebido) }}</td>
                            <td>{{ __moeda($valorAPagar) }}</td>
                            <td>{{ __data_pt($item->created_at) }}</td>
                            <td>{{ __data_pt($item->data_vencimento, false) }}</td>
                            <td>{{ $item->status ? __data_pt($item->data_recebimento, false) : '--' }}</td>
                            <td>
                                @if($item->status)
                                    <span class="btn btn-success btn-sm"><i class="bx bx-like"></i> Recebido</span>
                                @else
                                    <span class="btn btn-warning btn-sm"><i class="bx bx-error"></i>
                                        {{ \App\Models\StatusContaReceber::getStatusByVendaId($item->id) ?? 'Pendente' }}
                                    </span>
                                @endif
                            </td>
                            <td>
                                @if(!$item->status)
                                    <a href="{{ $linkPagamento }}" class="btn btn-primary btn-sm" target="_blank">Pagar Agora</a>
                                    <button class="btn btn-secondary btn-sm" onclick="copiarLink('{{ $linkPagamento }}')">Copiar Link</button>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center">Nada encontrado</td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr>
                        <th>Total Valor Integral:</th>
                        <th>{{ __moeda($totalValorIntegral) }}</th>
                        <th>{{ __moeda($totalValorAPagar) }}</th>
                        <th colspan="6"></th>
                    </tr>
                    <tr>
                        <th>Total Valor Recebido:</th>
                        <th>{{ __moeda($totalValorRecebido) }}</th>
                        <th colspan="7"></th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

{!! $data->appends(request()->all())->links() !!}
        </div>
    </div>
</div>
@endsection

@section('js')
<script>
function copiarLink(link) {
    navigator.clipboard.writeText(link).then(() => {
        alert('Link copiado com sucesso!');
    }).catch(err => {
        console.error('Erro ao copiar o link: ', err);
    });
}

function selecionarTodos() {
    $('.check').prop('checked', true);
    percorreTabela();
}

function selecionarAleatorios(qtd = 1) {
    $('.check').prop('checked', false);
    let checkboxes = $('.check').toArray();
    let total = checkboxes.length;
    if (qtd > total) qtd = total;
    checkboxes.sort(() => 0.5 - Math.random());
    for (let i = 0; i < qtd; i++) {
        checkboxes[i].checked = true;
    }
    percorreTabela();
}

$('#checkAll').on('click', function() {
    $('.check').prop('checked', this.checked);
    percorreTabela();
});

$(function() {
    percorreTabela();
});

$(".check").click(() => {
    percorreTabela();
});

function percorreTabela() {
    $('.btn-gerar-boletos').attr('disabled', true);
    $(".check").each(function() {
        if ($(this).is(":checked")) {
            $('.btn-gerar-boletos').removeAttr('disabled');
        }
    });
}
</script>
@endsection
