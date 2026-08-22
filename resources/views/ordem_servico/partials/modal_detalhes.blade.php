@php
    $numeroOs = $item->numero_sequencial ?? $item->id;
    $total = (float) ($item->valor ?? 0);
    $desconto = (float) ($item->desconto ?? 0);
    $entrada = (float) ($item->valor_entrada ?? 0);
    $pago = (float) ($item->valor_pago ?? 0);
    $liquido = max(0, $total - $desconto);
    $saldo = max(0, $liquido - $entrada - $pago);
@endphp
<div class="modal fade" id="modal-os-{{ $item->id }}" tabindex="-1" aria-labelledby="modal-os-label-{{ $item->id }}" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title" id="modal-os-label-{{ $item->id }}">Ordem de serviço #{{ $numeroOs }}</h5>
                    <small class="text-muted">Visualização e impressão térmica em 80 mm</small>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body bg-light">
                <div id="recibo-os-{{ $item->id }}" class="thermal-receipt mx-auto bg-white p-3">
                    <div class="text-center mb-2">
                        <strong class="d-block thermal-title">ORDEM DE SERVIÇO</strong>
                        <span>Nº {{ $numeroOs }}</span>
                    </div>
                    <div class="thermal-separator"></div>
                    <div><strong>Data:</strong> {{ optional($item->created_at)->format('d/m/Y H:i') }}</div>
                    <div><strong>Cliente:</strong> {{ optional($item->cliente)->razao_social ?? 'Não informado' }}</div>
                    @if(optional($item->cliente)->celular)
                        <div><strong>Telefone:</strong> {{ $item->cliente->celular }}</div>
                    @endif
                    <div><strong>Status:</strong> {{ mb_strtoupper($item->estado) }}</div>
                    <div class="thermal-separator"></div>
                    <div class="mb-2"><strong>DESCRIÇÃO</strong><br>{{ $item->descricao }}</div>

                    <strong>SERVIÇOS</strong>
                    @forelse($item->servicos as $servico)
                        <div class="thermal-item">
                            <div>{{ optional($servico->servico)->nome ?? 'Serviço removido' }}</div>
                            <div class="d-flex justify-content-between gap-2">
                                <span>{{ __moeda($servico->quantidade) }} x R$ {{ __moeda($servico->valor_unitario) }}</span>
                                <strong>R$ {{ __moeda($servico->sub_total) }}</strong>
                            </div>
                        </div>
                    @empty
                        <div class="text-muted">Nenhum serviço informado.</div>
                    @endforelse

                    @if($item->produtos->isNotEmpty())
                        <div class="thermal-separator"></div>
                        <strong>PRODUTOS</strong>
                        @foreach($item->produtos as $produto)
                            <div class="thermal-item">
                                <div>{{ optional($produto->produto)->nome ?? 'Produto removido' }}</div>
                                <div class="d-flex justify-content-between gap-2">
                                    <span>{{ __moeda($produto->quantidade) }} x R$ {{ __moeda($produto->valor_unitario) }}</span>
                                    <strong>R$ {{ __moeda($produto->sub_total) }}</strong>
                                </div>
                            </div>
                        @endforeach
                    @endif

                    <div class="thermal-separator"></div>
                    <div class="d-flex justify-content-between"><span>Subtotal:</span><strong>R$ {{ __moeda($total) }}</strong></div>
                    @if($desconto > 0)<div class="d-flex justify-content-between"><span>Desconto:</span><strong>- R$ {{ __moeda($desconto) }}</strong></div>@endif
                    <div class="d-flex justify-content-between thermal-total"><span>TOTAL:</span><strong>R$ {{ __moeda($liquido) }}</strong></div>
                    @if($entrada > 0)<div class="d-flex justify-content-between"><span>Entrada:</span><span>R$ {{ __moeda($entrada) }}</span></div>@endif
                    @if($pago > 0)<div class="d-flex justify-content-between"><span>Valor pago:</span><span>R$ {{ __moeda($pago) }}</span></div>@endif
                    <div class="d-flex justify-content-between"><span>Saldo:</span><strong>R$ {{ __moeda($saldo) }}</strong></div>
                    @if($item->forma_pagamento)<div><strong>Pagamento:</strong> {{ mb_strtoupper($item->forma_pagamento) }}</div>@endif
                    <div class="thermal-separator"></div>
                    <div><strong>Responsável:</strong> {{ optional($item->usuario)->nome ?? 'Não informado' }}</div>
                    <div class="mt-4">Assinatura: ________________________</div>
                    <div class="text-center mt-3">Obrigado pela preferência!</div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Fechar</button>
                <a href="{{ route('ordemServico.completa', $item->id) }}" class="btn btn-info text-white"><i class="bx bx-detail"></i> Abrir OS</a>
                <button type="button" class="btn btn-dark js-print-80" data-target="recibo-os-{{ $item->id }}"><i class="bx bx-printer"></i> Imprimir 80 mm</button>
            </div>
        </div>
    </div>
</div>