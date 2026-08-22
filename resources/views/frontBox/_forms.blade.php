@php
    $prevenda = $item ?? null;
    $aberturaValor = isset($abertura)
        ? (is_object($abertura) ? data_get($abertura, 'id', 1) : $abertura)
        : '';
    $filialId = isset($filial)
        ? (is_object($filial) ? data_get($filial, 'id') : $filial)
        : null;
    $valorTotalInicial = $valor_total ?? data_get($prevenda, 'valor_total', 0);
@endphp

<input type="hidden" id="caixa_livre" value="{{ data_get($usuario, 'caixa_livre', 0) }}">
<input type="hidden" id="abertura" value="{{ $aberturaValor }}">
<input type="hidden" id="prevenda_id" name="prevenda_id" value="{{ data_get($prevenda, 'id') }}">
<input type="hidden" id="valor_total" value="{{ $valorTotalInicial }}">

@if(isset($itens))
    <input
        type="hidden"
        id="itens_pedido"
        value="{{ json_encode($itens, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) }}"
    >
    <input type="hidden" id="delivery_id" value="{{ $delivery_id ?? 0 }}">
    <input type="hidden" id="bairro" value="{{ $bairro ?? 0 }}">
    <input type="hidden" id="codigo_comanda_hidden" value="{{ $cod_comanda ?? 0 }}">
@endif

<input type="hidden" id="codigo_comanda" name="codigo_comanda" value="0">
<input type="hidden" name="pdv_token" value="{{ (string) \Illuminate\Support\Str::uuid() }}">
<input type="hidden" id="pix_payment_id" name="pix_payment_id" value="">
<input type="hidden" id="pix_valor" name="pix_valor" value="">

@isset($pedido)
    <input type="hidden" name="pedido_id" value="{{ $pedido->id }}">
@endisset

@isset($filial)
    <input type="hidden" id="filial" class="filial_id" name="filial_id" value="{{ $filialId }}">
@endisset

<div class="pdv-workspace">
    <div class="pdv-command-bar">
        <div class="pdv-command-status">
            <div class="pdv-command-icon" aria-hidden="true">
                <i class="bx bx-store"></i>
            </div>

            <div class="pdv-command-copy">
                <div class="pdv-command-title">
                    <strong id="timer">--:--:--</strong>

                    @if(data_get($usuario, 'caixa_livre'))
                        <span class="pdv-badge pdv-badge-info">Caixa livre</span>
                    @else
                        <span class="pdv-badge pdv-badge-success">Caixa em operação</span>
                    @endif
                </div>

                <small>Atendimento rápido por código de barras ou pesquisa de produto</small>
            </div>

            @if(data_get($usuario, 'caixa_livre'))
                <button
                    type="button"
                    class="pdv-icon-action"
                    data-bs-toggle="modal"
                    data-bs-target="#modal-funcionarios"
                    title="Selecionar funcionário"
                    aria-label="Selecionar funcionário"
                >
                    <i class="bx bx-user"></i>
                </button>
            @endif

            <span class="h4-comanda pdv-comanda-label"></span>
        </div>

        <div class="pdv-command-actions" aria-label="Ações rápidas do caixa">
            <button
                type="button"
                class="pdv-command-button pdv-command-dark"
                data-bs-toggle="modal"
                data-bs-target="#modal-selecionar_vendedor"
                title="Informar vendedor"
            >
                <i class="bx bx-user-check"></i>
                <span>Vendedor</span>
            </button>

            <button
                type="button"
                class="pdv-command-button pdv-command-info"
                data-bs-toggle="modal"
                data-bs-target="#modal-lista_pre_venda"
                title="Abrir pré-vendas"
            >
                <i class="bx bx-folder-open"></i>
                <span>Pré-vendas</span>
            </button>

            <a
                href="{{ route('frenteCaixa.list') }}"
                class="pdv-command-button pdv-command-primary"
                title="Consultar vendas"
            >
                <i class="bx bx-list-check"></i>
                <span>Vendas</span>
            </a>

            <button
                type="button"
                class="pdv-command-button pdv-command-warning"
                data-bs-toggle="modal"
                data-bs-target="#modal-fluxo_diario"
                title="Ver fluxo diário"
            >
                <i class="bx bx-money"></i>
                <span>Fluxo</span>
            </button>

            <a
                href="{{ route('frenteCaixa.troca') }}"
                class="pdv-command-button pdv-command-success"
                title="Consultar trocas"
            >
                <i class="bx bx-sync"></i>
                <span>Trocas</span>
            </a>
        </div>

        <div class="pdv-command-menus">
            <div class="dropdown">
                <button
                    type="button"
                    class="pdv-menu-button dropdown-toggle"
                    data-bs-toggle="dropdown"
                    aria-expanded="false"
                >
                    <i class="bx bx-grid-alt"></i>
                    Ações
                </button>

                <ul class="dropdown-menu dropdown-menu-end">
                    <li>
                        <a class="dropdown-item" href="{{ route('frenteCaixa.devolucao') }}">
                            <i class="bx bx-undo"></i>
                            Devolução
                        </a>
                    </li>
                    <li>
                        <button type="button" class="dropdown-item" data-bs-toggle="modal" data-bs-target="#modal-sangria_caixa">
                            <i class="bx bx-down-arrow-circle"></i>
                            Sangria
                        </button>
                    </li>
                    <li>
                        <button type="button" class="dropdown-item" data-bs-toggle="modal" data-bs-target="#modal-suprimento_caixa">
                            <i class="bx bx-up-arrow-circle"></i>
                            Suprimento de caixa
                        </button>
                    </li>
                    <li>
                        <button type="button" class="dropdown-item" data-bs-toggle="modal" data-bs-target="#modal-comanda_pdv">
                            <i class="bx bx-receipt"></i>
                            Apontar comanda
                        </button>
                    </li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <a class="dropdown-item" href="{{ route('frenteCaixa.fechar') }}">
                            <i class="bx bx-lock-alt"></i>
                            Fechar caixa
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item" href="{{ route('frenteCaixa.configuracao') }}">
                            <i class="bx bx-cog"></i>
                            Configuração
                        </a>
                    </li>
                </ul>
            </div>

            <a class="pdv-exit-button" href="{{ route('vendas.index') }}" title="Sair do PDV">
                <i class="bx bx-log-out"></i>
                <span>Sair</span>
            </a>
        </div>
    </div>

    <div class="pdv-layout">
        <main class="pdv-products">
            <label class="pdv-scanner" id="focus-codigo" for="codBarras">
                <i class="bx bx-barcode-reader" aria-hidden="true"></i>

                <input
                    class="mousetrap"
                    type="text"
                    id="codBarras"
                    inputmode="none"
                    autocomplete="off"
                    autofocus
                    aria-label="Leitor de código de barras"
                >

                <span id="mousetrapTitle">
                    <strong class="texto-leitor">Clique aqui para ativar o leitor</strong>
                    <small>Leia o código ou pesquise abaixo pelo nome, referência ou descrição.</small>
                </span>

                <span class="pdv-scanner-status">
                    <i class="bx bx-scan"></i>
                    Leitor pronto
                </span>
            </label>

            <section class="pdv-product-form" aria-label="Adicionar produto">
                <div class="pdv-product-field">
                    <label for="inp-produto_id">Produto</label>
                    <select
                        class="form-select"
                        name="produto_id"
                        id="inp-produto_id"
                        aria-label="Pesquisar produto"
                    ></select>
                </div>

                <div>
                    {!! Form::tel('quantidade', 'Quantidade')->attrs([
                        'class' => 'qtd',
                        'inputmode' => 'decimal',
                        'autocomplete' => 'off'
                    ]) !!}
                </div>

                <div>
                    {!! Form::tel('valor_unitario', 'Valor unitário')->attrs([
                        'class' => 'moeda value_unit',
                        'inputmode' => 'decimal',
                        'autocomplete' => 'off'
                    ]) !!}
                </div>

                <div class="pdv-add-field">
                    <button class="btn btn-primary btn-add-item" type="button">
                        <i class="bx bx-plus"></i>
                        Adicionar
                    </button>
                </div>
            </section>

            <section class="pdv-items-card" aria-labelledby="pdv-items-title">
                <header class="pdv-items-heading">
                    <div>
                        <h2 id="pdv-items-title">Itens da venda</h2>
                        <small>Confira quantidades e valores antes de finalizar.</small>
                    </div>

                    <span class="pdv-items-count">
                        <i class="bx bx-package"></i>
                        <strong id="pdv-items-count">0</strong>
                        itens
                    </span>
                </header>

                <div class="pdv-items-scroll">
                    <table class="table table-striped table-itens table-pdv align-middle">
                        <thead>
                            <tr>
                                <th>Produto</th>
                                <th>Quantidade</th>
                                <th>Valor unitário</th>
                                <th>Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @isset($itensDopedido)
                                @foreach($itensDopedido as $itemPedidoHtml)
                                    {!! $itemPedidoHtml !!}
                                @endforeach
                            @endisset

                            @if($prevenda)
                                @foreach($prevenda->itens as $product)
                                    <tr>
                                        <td>
                                            <input readonly type="hidden" name="key" value="{{ $product->key }}">
                                            <input readonly type="hidden" name="produto_id[]" value="{{ $product->produto->id }}">
                                            <input
                                                readonly
                                                type="text"
                                                name="produto_nome[]"
                                                class="form-control"
                                                value="{{ $product->produto->nome }}"
                                                title="{{ $product->produto->nome }}"
                                            >
                                        </td>
                                        <td>
                                            <input
                                                readonly
                                                type="tel"
                                                name="quantidade[]"
                                                class="form-control qtd-item"
                                                value="{{ __estoque($product->quantidade) }}"
                                            >
                                        </td>
                                        <td>
                                            <input
                                                readonly
                                                type="tel"
                                                name="valor_unitario[]"
                                                class="form-control"
                                                value="{{ __moeda($product->valor) }}"
                                            >
                                        </td>
                                        <td>
                                            <input
                                                readonly
                                                type="tel"
                                                name="subtotal_item[]"
                                                class="form-control subtotal-item"
                                                value="{{ __moeda($product->valor * $product->quantidade) }}"
                                            >
                                        </td>
                                    </tr>
                                @endforeach
                            @endif
                        </tbody>
                    </table>

                    <div class="pdv-empty-state d-none" id="pdv-empty-products">
                        <i class="bx bx-cart"></i>
                        <strong>A venda ainda está vazia</strong>
                        <span>Use o leitor ou pesquise um produto para começar.</span>
                    </div>
                </div>
            </section>

            <section class="pdv-adjustments" aria-label="Ajustes da venda">
                <button type="button" onclick="setaDesconto()" class="pdv-adjustment-card btn-desconto">
                    <span class="pdv-adjustment-icon"><i class="bx bx-purchase-tag"></i></span>
                    <span class="pdv-adjustment-copy">
                        <small>Desconto aplicado</small>
                        <strong class="class_desconto" id="valor_desconto">0,00%</strong>
                    </span>
                    <i class="bx bx-edit pdv-adjustment-edit"></i>
                </button>

                <button type="button" onclick="setaAcrescimo()" class="pdv-adjustment-card btn-acrescimo">
                    <span class="pdv-adjustment-icon"><i class="bx bx-plus-circle"></i></span>
                    <span class="pdv-adjustment-copy">
                        <small>Acréscimo aplicado</small>
                        <strong class="class_acrescimo" id="valor_acrescimo">R$ 0,00</strong>
                    </span>
                    <i class="bx bx-edit pdv-adjustment-edit"></i>
                </button>

                <div class="pdv-price-list">
                    <label for="lista-precos-pdv">Lista de preços</label>
                    <select id="lista-precos-pdv" class="form-select" aria-label="Lista de preços">
                        @forelse(($lista ?? []) as $listaPreco)
                            <option value="{{ $listaPreco->id ?? '' }}">{{ $listaPreco->nome }}</option>
                        @empty
                            <option value="">Lista padrão</option>
                        @endforelse
                    </select>
                </div>

                @if(isset($abertura) && empresaComFilial() && $abertura)
                    <div class="pdv-location">
                        <i class="bx bx-map"></i>
                        Local de atendimento
                        <strong>{{ data_get($filial, 'descricao', 'Matriz') }}</strong>
                    </div>
                @endif
            </section>
        </main>

        <aside class="pdv-checkout">
            <div class="pdv-checkout-actions">
                <button
                    type="button"
                    id="btn-seleciona-cliente"
                    data-bs-toggle="modal"
                    data-bs-target="#modal-selecionar_cliente"
                    class="pdv-quick-action pdv-client-action btn-selecionar_cliente"
                >
                    <i class="bx bx-user"></i>
                    <span>Cliente</span>
                    <small>Selecionar cadastro</small>
                </button>

                <button
                    type="button"
                    class="pdv-quick-action pdv-multi-action modal-pag_mult"
                    data-bs-toggle="modal"
                    data-bs-target="#modal-pag_multi_pdv"
                >
                    <i class="bx bx-list-ol"></i>
                    <span>Pagamento</span>
                    <small>Dividir formas</small>
                </button>

                <button
                    type="button"
                    class="pdv-quick-action pdv-note-action"
                    data-bs-toggle="modal"
                    data-bs-target="#modal-observacoes_pdv"
                >
                    <i class="bx bx-pencil"></i>
                    <span>Observação</span>
                    <small>Adicionar nota</small>
                </button>
            </div>

            <section class="pdv-total-card" aria-label="Total da venda">
                <div class="pdv-total-label">
                    <span>Total da venda</span>
                    <i class="bx bx-wallet"></i>
                </div>

                <div class="pdv-total-value">
                    <small>R$</small>
                    <strong class="total-venda">
                        {{ $prevenda ? __moeda($prevenda->valor_total) : '0,00' }}
                    </strong>
                </div>
            </section>

            <section class="pdv-payment-section" aria-labelledby="pdv-payment-title">
                <header class="pdv-section-heading">
                    <div>
                        <h2 id="pdv-payment-title">Recebimento</h2>
                        <small>Escolha a forma de pagamento.</small>
                    </div>
                    <i class="bx bx-credit-card"></i>
                </header>

                <div class="pdv-payment-field">
                    {!! Form::select(
                        'tipo_pagamento',
                        'Tipo de pagamento',
                        ['' => 'Selecione'] + App\Models\Venda::tiposPagamento()
                    )->attrs([
                        'class' => 'select2',
                        'id' => 'tipo_pagamento'
                    ]) !!}
                </div>

                <div id="credito-cliente-pdv" class="pdv-credit-panel d-none" role="alert" aria-live="polite"></div>

                <div class="pdv-installments div-vencimento d-none" id="opcoes-crediario-pdv">
                    <div class="pdv-installment-grid">
                        <div>
                            {!! Form::date(
                                'data_vencimento',
                                null,
                                'Primeiro vencimento',
                                ['class' => 'form-control']
                            ) !!}
                        </div>

                        <div>
                            <label for="qd-parcelas-pdv">Quantidade de parcelas</label>
                            <select name="qd_parcelas" id="qd-parcelas-pdv" class="form-select parcelas-input">
                                <option value="">Selecione</option>
                                @for($parcela = 1; $parcela <= 12; $parcela++)
                                    <option value="{{ $parcela }}">{{ $parcela }}x</option>
                                @endfor
                            </select>
                        </div>
                    </div>

                    <div id="parcelas-simulacao" class="pdv-installment-preview"></div>
                </div>

                <div class="pdv-received-field">
                    <label for="valor_recebido">Valor recebido</label>
                    <div class="pdv-money-input">
                        <span>R$</span>
                        <input
                            type="text"
                            id="valor_recebido"
                            name="valor_recebido"
                            placeholder="0,00"
                            class="form-control moeda"
                            autocomplete="off"
                            inputmode="decimal"
                        >
                    </div>
                </div>

                <div class="pdv-change-card div-troco div-toco">
                    <div>
                        <small>Troco</small>
                        <strong id="valor-troco">R$ 0,00</strong>
                    </div>
                    <i class="bx bx-transfer-alt"></i>
                </div>

                {!! Form::hidden('subtotal', 'SubTotal')->attrs(['class' => 'moeda']) !!}

                <button
                    type="button"
                    id="salvar_venda"
                    disabled
                    class="btn btn-success pdv-finish-button"
                    data-bs-toggle="modal"
                    data-bs-target="#modal-finalizar_venda"
                >
                    <span><i class="bx bx-check-circle"></i> Finalizar venda</span>
                    <small>Revise o pagamento antes de concluir</small>
                </button>
            </section>
        </aside>
    </div>
</div>

<div class="modal fade" id="modal-pix" tabindex="-1" aria-labelledby="modal-pix-title" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title" id="modal-pix-title">Pagamento via PIX</h5>
                    <small class="text-muted">A confirmação será feita automaticamente.</small>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body text-center">
                <div class="pdv-pix-box">
                    <img id="qr-code-img" src="" alt="QR Code PIX" class="img-fluid">
                    <p class="mt-3 mb-2 text-muted">
                        Abra o aplicativo do banco e escaneie o QR Code para pagar.
                    </p>
                    <div id="pix-status-message" class="alert alert-info mb-0" role="status">
                        Aguardando pagamento...
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Fechar</button>
            </div>
        </div>
    </div>
</div>

@include('modals.frontBox._selecionar_cliente', ['not_submit' => true])
@include('modals.frontBox._observacoes_pdv', ['not_submit' => true])
@include('modals.frontBox._selecionar_vendedor', ['not_submit' => true])
@include('modals.frontBox._pag_multi_pdv', ['not_submit' => true])
@include('modals.frontBox._finalizar_venda')
@include('modals.frontBox._dados_cartao')

@once
<script>
    document.addEventListener('DOMContentLoaded', function () {
        'use strict';

        var totalVendaElement = document.querySelector('.total-venda');
        var parcelasInput = document.getElementById('qd-parcelas-pdv');
        var parcelasContainer = document.getElementById('parcelas-simulacao');
        var tabelaItens = document.querySelector('.table-itens tbody');
        var contadorItens = document.getElementById('pdv-items-count');
        var estadoVazio = document.getElementById('pdv-empty-products');
        var tipoPagamento = document.getElementById('inp-tipo_pagamento')
            || document.querySelector('[name="tipo_pagamento"]');
        var modalPixElement = document.getElementById('modal-pix');
        var pixTimer = null;
        var pixGerando = false;
        var pixTentativas = 0;

        function moedaParaFloat(valor) {
            valor = String(valor || '').replace(/\s/g, '').replace('R$', '');
            if (valor.indexOf(',') !== -1) {
                valor = valor.replace(/\./g, '').replace(',', '.');
            }
            return parseFloat(valor.replace(/[^0-9.-]/g, '')) || 0;
        }

        function formatarMoeda(valor) {
            return Number(valor || 0).toLocaleString('pt-BR', {
                style: 'currency',
                currency: 'BRL'
            });
        }

        function totalAtual() {
            return totalVendaElement ? moedaParaFloat(totalVendaElement.textContent) : 0;
        }

        function atualizarContadorItens() {
            if (!tabelaItens) {
                return;
            }

            var quantidade = tabelaItens.querySelectorAll('tr').length;
            if (contadorItens) {
                contadorItens.textContent = quantidade;
            }
            if (estadoVazio) {
                estadoVazio.classList.toggle('d-none', quantidade > 0);
            }
        }

        function calcularParcelas() {
            if (!parcelasInput || !parcelasContainer) {
                return;
            }

            parcelasContainer.innerHTML = '';
            var quantidade = parseInt(parcelasInput.value, 10) || 0;
            var totalCentavos = Math.round(totalAtual() * 100);

            if (quantidade <= 0 || totalCentavos <= 0) {
                return;
            }

            var base = Math.floor(totalCentavos / quantidade);
            var diferenca = totalCentavos - (base * quantidade);

            for (var numero = 1; numero <= quantidade; numero++) {
                var centavos = base + (numero === quantidade ? diferenca : 0);
                var linha = document.createElement('div');
                linha.className = 'pdv-installment-line';
                linha.innerHTML = '<span>Parcela ' + numero + ' de ' + quantidade + '</span>'
                    + '<strong>' + formatarMoeda(centavos / 100) + '</strong>';
                parcelasContainer.appendChild(linha);
            }
        }

        function aviso(titulo, texto, icone) {
            if (typeof window.swal === 'function') {
                window.swal(titulo, texto, icone || 'warning');
                return;
            }
            window.alert(texto);
        }

        function urlPdv(caminho) {
            var base = typeof path_url !== 'undefined' ? String(path_url) : '/';
            return base.replace(/\/?$/, '/') + String(caminho).replace(/^\//, '');
        }

        function redefinirConfirmacaoPix() {
            window.__pdvPixQrConfirmado = false;
            var pagamento = document.getElementById('pix_payment_id');
            var valor = document.getElementById('pix_valor');
            if (pagamento) pagamento.value = '';
            if (valor) valor.value = '';
        }

        function statusPix(texto, classe) {
            var status = document.getElementById('pix-status-message');
            if (!status) {
                return;
            }
            status.className = 'alert mb-0 ' + (classe || 'alert-info');
            status.textContent = texto;
        }

        function pararPix() {
            if (pixTimer) {
                window.clearTimeout(pixTimer);
                pixTimer = null;
            }
            pixTentativas = 0;
        }

        function verificarStatusPix(idPagamento) {
            pararPix();

            function consultar() {
                if (pixTentativas >= 120) {
                    statusPix('Tempo de confirmação encerrado. Consulte novamente.', 'alert-warning');
                    pararPix();
                    return;
                }

                pixTentativas++;
                fetch(urlPdv('pix/status-pagamento/' + encodeURIComponent(idPagamento))
                    + '?empresa_id=' + encodeURIComponent(document.getElementById('empresa_id').value), {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                })
                    .then(function (resposta) {
                        return resposta.json().then(function (dados) {
                            if (!resposta.ok) {
                                throw new Error(dados.message || dados.erro || 'Falha ao consultar o PIX.');
                            }
                            return dados;
                        });
                    })
                    .then(function (dados) {
                        var status = String(dados.status || '').toLowerCase();
                        if (['pago', 'paid', 'approved', 'aprovado'].indexOf(status) !== -1) {
                            pararPix();
                            window.__pdvPixQrConfirmado = true;
                            document.getElementById('pix_payment_id').value = String(idPagamento);
                            statusPix('Pagamento confirmado com sucesso!', 'alert-success');
                            aviso('Pagamento confirmado', 'O PIX foi aprovado com sucesso.', 'success');

                            if (modalPixElement && window.bootstrap) {
                                var modalPix = bootstrap.Modal.getInstance(modalPixElement);
                                if (modalPix) {
                                    modalPix.hide();
                                }
                            }

                            if (typeof window.validateButtonSave === 'function') {
                                window.validateButtonSave();
                            }

                            var modalFinalizar = document.getElementById('modal-finalizar_venda');
                            if (modalFinalizar && window.bootstrap) {
                                bootstrap.Modal.getOrCreateInstance(modalFinalizar).show();
                            }
                            return;
                        }

                        pixTimer = window.setTimeout(consultar, 5000);
                    })
                    .catch(function () {
                        pixTimer = window.setTimeout(consultar, 5000);
                    });
            }

            consultar();
        }

        function gerarPix() {
            if (pixGerando || totalAtual() <= 0) {
                if (totalAtual() <= 0) {
                    aviso('Venda sem valor', 'Adicione produtos antes de gerar o PIX.', 'warning');
                }
                return;
            }

            pixGerando = true;
            pararPix();
            redefinirConfirmacaoPix();
            statusPix('Gerando QR Code...', 'alert-info');
            if (typeof window.abrirCarregamentoPdv === 'function') {
                window.abrirCarregamentoPdv(
                    'Gerando PIX',
                    'Aguarde enquanto o Mercado Pago prepara o QR Code.'
                );
            }

            var empresa = document.getElementById('empresa_id').value;
            var valorPix = totalAtual().toFixed(2);
            var tokenPdv = document.querySelector('[name="pdv_token"]').value;
            var csrf = document.querySelector('meta[name="csrf-token"]');
            var parametros = new URLSearchParams({
                empresa_id: empresa,
                valor: valorPix,
                pdv_token: tokenPdv
            });

            fetch(urlPdv('pix/gerar'), {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': csrf ? csrf.content : ''
                },
                body: parametros.toString()
            })
                .then(function (resposta) {
                    return resposta.json().then(function (dados) {
                        if (!resposta.ok) {
                            throw new Error(dados.message || dados.erro || 'Não foi possível gerar o PIX.');
                        }
                        return dados;
                    });
                })
                .then(function (dados) {
                    if (!dados.qr_code_base64) {
                        throw new Error(dados.message || 'PIX não habilitado para esta empresa.');
                    }

                    if (typeof window.fecharCarregamentoPdv === 'function') {
                        window.fecharCarregamentoPdv();
                    }
                    document.getElementById('qr-code-img').src = 'data:image/png;base64,' + dados.qr_code_base64;
                    document.getElementById('pix_valor').value = valorPix;
                    statusPix('Aguardando confirmação do pagamento...', 'alert-info');

                    if (modalPixElement && window.bootstrap) {
                        bootstrap.Modal.getOrCreateInstance(modalPixElement).show();
                    }

                    if (dados.id_pagamento) {
                        verificarStatusPix(dados.id_pagamento);
                    }
                })
                .catch(function (erro) {
                    if (typeof window.fecharCarregamentoPdv === 'function') {
                        window.fecharCarregamentoPdv();
                    }
                    console.error(erro);
                    statusPix('Não foi possível gerar o PIX.', 'alert-danger');
                    aviso('Erro ao gerar PIX', erro.message, 'error');
                })
                .finally(function () {
                    pixGerando = false;
                });
        }

        if (tabelaItens) {
            new MutationObserver(atualizarContadorItens).observe(tabelaItens, {
                childList: true,
                subtree: false
            });
        }

        if (totalVendaElement) {
            new MutationObserver(calcularParcelas).observe(totalVendaElement, {
                childList: true,
                characterData: true,
                subtree: true
            });
        }

        if (parcelasInput) {
            parcelasInput.addEventListener('change', calcularParcelas);
        }

        if (tipoPagamento) {
            tipoPagamento.addEventListener('change', function () {
                var tipo = String(this.value || '');
                pararPix();
                redefinirConfirmacaoPix();

                if (tipo === '19') {
                    gerarPix();
                    return;
                }

                if (tipo === '17') {
                    statusPix('PIX direto selecionado. Confirme o recebimento antes de finalizar.', 'alert-info');
                }

                if (typeof window.validateButtonSave === 'function') {
                    window.validateButtonSave();
                }
            });
        }

        if (modalPixElement) {
            modalPixElement.addEventListener('hidden.bs.modal', function () {
                if (String(tipoPagamento ? tipoPagamento.value : '') !== '19') {
                    pararPix();
                }
            });
        }

        atualizarContadorItens();
        calcularParcelas();
    });
</script>
@endonce