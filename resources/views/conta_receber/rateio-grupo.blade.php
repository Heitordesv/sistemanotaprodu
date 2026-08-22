@extends('default.layout', ['title' => 'Rateio por Grupo'])

@section('content')
<div class="container mt-5">
    <div class="card border-0 shadow-lg">
        <div class="card-header bg-gradient-primary text-white py-3" style="background: linear-gradient(45deg, #4e73df, #224abe);">
            <h5 class="mb-0 font-weight-bold">
                <i class="fas fa-file-invoice-dollar mr-2"></i> Rateio por Grupo de Clientes
            </h5>
        </div>

        <div class="card-body p-4">
            <form id="form-rateio" method="POST" action="{{ route('conta-receber.rateio-grupo.store') }}">
                @csrf
                <input type="hidden" name="empresa_id" value="{{ $empresa_id }}">

                <div class="row mb-4">
                    <div class="col-md-12">
                        <h6 class="text-uppercase text-muted font-weight-bold small mb-3">Informações Principais</h6>
                        <hr class="mt-0">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label font-weight-bold">Valor Total</label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text bg-light text-primary font-weight-bold">R$</span>
                            </div>
                            <input type="text" id="valor_total" name="valor_total" class="form-control form-control-lg money" placeholder="0,00" required>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label font-weight-bold">Parcelas</label>
                        <input type="number" id="parcelas" name="parcelas" class="form-control form-control-lg" value="1" min="1">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label font-weight-bold">1º Vencimento</label>
                        <input type="date" name="data_vencimento" class="form-control form-control-lg" value="{{ date('Y-m-d') }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label font-weight-bold">Categoria de Lançamento</label>
                        <select name="categoria_id" class="form-control form-control-lg custom-select" required>
                            <option value="">Selecione...</option>
                            @foreach($categorias as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->nome }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 mt-3">
                        <label class="form-label font-weight-bold">Forma de Pagamento</label>
                        <select name="tipo_pagamento" class="form-control form-control-lg custom-select" required>
                            <option value="">Selecione...</option>
                            <option value="dinheiro">Dinheiro</option>
                            <option value="boleto">Boleto</option>
                            <option value="cartao_credito">Cartão de Crédito</option>
                            <option value="cartao_debito">Cartão de Débito</option>
                            <option value="pix">PIX</option>
                            <option value="transferencia">Transferência</option>
                        </select>
                    </div>
                </div>

                <div class="row mb-4 align-items-end">
                    <div class="col-md-12">
                        <h6 class="text-uppercase text-muted font-weight-bold small mb-3">Definição do Rateio</h6>
                        <hr class="mt-0">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label font-weight-bold text-primary">Grupo Destinatário</label>
                        <select id="grupo_id" name="grupo_id" class="form-control form-control-lg border-primary" required>
                            <option value="">Escolha um grupo para carregar os clientes...</option>
                            @foreach($grupos as $g)
                                <option value="{{ $g->id }}">{{ $g->nome }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6 text-right">
                        <button type="button" id="btn-igual" class="btn btn-outline-primary btn-sm px-4">
                            <i class="fas fa-divide mr-1"></i> Distribuir Igualmente
                        </button>
                    </div>
                </div>

                <div id="clientes-container" class="table-responsive mt-2" style="min-height: 100px;">
                    <div class="text-center p-5 text-muted border rounded bg-light">
                        <i class="fas fa-users fa-3x mb-3 opacity-50"></i>
                        <p>Selecione um grupo acima para listar os clientes.</p>
                    </div>
                </div>

                <div class="alert alert-secondary mt-4 d-flex justify-content-between align-items-center shadow-sm">
                    <div class="text-left">
                        <small class="d-block text-uppercase">Status do Rateio:</small>
                        <strong id="total_percent" class="h4 mb-0">0.00%</strong>
                    </div>
                    <div class="text-center border-left border-right px-4">
                        <small class="d-block text-uppercase">Total Calculado:</small>
                        <strong id="total_valor" class="h4 mb-0">R$ 0,00</strong>
                    </div>
                    <div>
                        <button type="submit" class="btn btn-success btn-lg px-5 shadow">
                            <i class="fas fa-check-circle mr-2"></i> Confirmar e Gerar
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const grupoSelect = document.getElementById('grupo_id');
        const container = document.getElementById('clientes-container');
        const valorTotalInput = document.getElementById('valor_total');
        const totalPercentLabel = document.getElementById('total_percent');
        const totalValorLabel = document.getElementById('total_valor');

        // Busca de Clientes com Feedback Visual
        grupoSelect.addEventListener('change', function() {
            const grupoId = this.value;
            if (!grupoId) return;

            container.innerHTML = `
                <div class="text-center p-5">
                    <div class="spinner-border text-primary" role="status"></div>
                    <p class="mt-2">Carregando membros do grupo...</p>
                </div>`;

            fetch("/conta-receber/grupo-clientes/" + grupoId)
                .then(response => response.json())
                .then(data => {
                    if (data.length === 0) {
                        container.innerHTML = "<div class='alert alert-warning border-0 shadow-sm'>Nenhum cliente vinculado a este grupo.</div>";
                        return;
                    }

                    let html = `
                        <table class="table table-hover align-middle border">
                            <thead class="thead-light">
                                <tr>
                                    <th>Cliente / Razão Social</th>
                                    <th width="200">Percentual (%)</th>
                                    <th width="200" class="text-right">Valor Individual</th>
                                </tr>
                            </thead>
                            <tbody>`;

                    data.forEach(c => {
                        html += `
                        <tr>
                            <td class="align-middle font-weight-bold text-dark">
                                ${c.razao_social} 
                                <input type="hidden" name="clientes[${c.id}][id]" value="${c.id}">
                            </td>
                            <td>
                                <div class="input-group">
                                    <input type="number" step="0.01" class="form-control input-percent" 
                                           name="clientes[${c.id}][percentual]" value="0" min="0" max="100">
                                    <div class="input-group-append"><span class="input-group-text">%</span></div>
                                </div>
                            </td>
                            <td class="text-right align-middle font-weight-bold text-primary valor-calculado">
                                R$ 0,00
                            </td>
                        </tr>`;
                    });

                    html += `</tbody></table>`;
                    container.innerHTML = html;
                    calcularTudo();
                });
        });

        // Cálculos e Estilização de Erro
        document.addEventListener('input', function(e) {
            if (e.target.classList.contains('input-percent') || e.target.id === 'valor_total') {
                calcularTudo();
            }
        });

        function calcularTudo() {
            const valorTotal = parseMoeda(valorTotalInput.value);
            const percents = document.querySelectorAll('.input-percent');
            let somaP = 0;

            percents.forEach(input => {
                const p = parseFloat(input.value) || 0;
                const valorCli = (valorTotal * p) / 100;
                input.closest('tr').querySelector('.valor-calculado').innerText = formatarMoeda(valorCli);
                somaP += p;
            });

            totalPercentLabel.innerText = somaP.toFixed(2) + "%";
            totalValorLabel.innerText = formatarMoeda((valorTotal * somaP) / 100);

            // Feedback visual de erro (Soma deve ser 100%)
            if (Math.abs(somaP - 100) > 0.01) {
                totalPercentLabel.classList.add('text-danger');
                totalPercentLabel.classList.remove('text-success');
            } else {
                totalPercentLabel.classList.remove('text-danger');
                totalPercentLabel.classList.add('text-success');
            }
        }

        document.getElementById('btn-igual').addEventListener('click', function() {
            const percents = document.querySelectorAll('.input-percent');
            if (percents.length === 0) return;

            const base = Math.floor((100 / percents.length) * 100) / 100;
            percents.forEach(input => input.value = base);
            
            const sobra = (100 - (base * percents.length)).toFixed(2);
            if (sobra > 0) {
                percents[percents.length - 1].value = (parseFloat(percents[percents.length - 1].value) + parseFloat(sobra)).toFixed(2);
            }
            calcularTudo();
        });

        function parseMoeda(v) {
            if (!v) return 0;
            if (typeof v === 'number') return v;
            return parseFloat(v.replace(/\./g, '').replace(',', '.')) || 0;
        }

        function formatarMoeda(v) {
            return v.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
        }
    });
</script>

<style>
    .form-control-lg { font-size: 1rem; }
    .bg-gradient-primary { background: linear-gradient(45deg, #4e73df 10%, #224abe 100%); }
    .shadow-lg { box-shadow: 0 1rem 3rem rgba(0,0,0,.175)!important; }
    .table thead th { border-top: none; text-transform: uppercase; font-size: 0.8rem; letter-spacing: 1px; }
    .money:focus { border-color: #4e73df; box-shadow: 0 0 0 0.2rem rgba(78,115,223,.25); }
</style>
@endsection