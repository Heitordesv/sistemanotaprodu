@extends('default.layout', ['title' => 'Pedidos'])
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" integrity="sha512-..." crossorigin="anonymous" referrerpolicy="no-referrer" />
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
@section('content')
<meta name="csrf-token" content="{{ csrf_token() }}">
@push('styles')
<style>
    .button-group-status .btn {
        min-width: 120px; /* Ajuste este valor */
        text-align: center;
        display: flex;
        align-items: center;
        justify-content: center;
    }
</style>
@endpush
<style>/* Cores para os botões de status personalizados */
.btn-status-ver { /* Para o botão "Confirmar Pedido" (Em Andamento) */
    background-color: #28a745; /* Um verde um pouco mais vibrante */
    border-color: #28a745;
    color: white; /* Cor do texto para contraste */
}


.btn-status-confirmar { /* Para o botão "Confirmar Pedido" (Em Andamento) */
    background-color: #28a745; /* Um verde um pouco mais vibrante */
    border-color: #28a745;
    color: white; /* Cor do texto para contraste */
}
.btn-status-confirmar:hover {
    background-color: #218838;
    border-color: #1e7e34;
}

.btn-status-entrega { /* Para o botão "Saiu para Entrega" */
    background-color: #ffc107; /* Um amarelo/laranja para entrega */
    border-color: #ffc107;
    color: #212529; /* Cor do texto escuro para contraste */
}
.btn-status-entrega:hover {
    background-color: #e0a800;
    border-color: #d39e00;
}

.btn-status-retirada { /* Para o botão "Retirada" */
    background-color: #17a2b8; /* Um ciano/azul para retirada */
    border-color: #17a2b8;
    color: white;
}
.btn-status-retirada:hover {
    background-color: #138496;
    border-color: #117a8b;
}

.btn-status-finalizar { /* Para o botão "Finalizar" */
    background-color: #6f42c1; /* Um roxo */
    border-color: #6f42c1;
    color: white;
}
.btn-status-finalizar:hover {
    background-color: #5b32a2;
    border-color: #542d96;
}

.btn-status-cancelar { /* Para o botão "Cancelar" */
    background-color: #dc3545; /* Vermelho padrão do Bootstrap */
    border-color: #dc3545;
    color: white;
}
.btn-status-cancelar:hover {
    background-color: #c82333;
    border-color: #bd2130;
}
</style>

<div class="page-content">
    <div class="card">
        <div class="card-body p-4">
            <div class="page-breadcrumb d-sm-flex align-items-center mb-3">
                <div class="ms-auto p-3 rounded shadow-sm bg-light" style="max-width: 350px;">
                    
                     @if ($datadodia && date('Y-m-d') === date('Y-m-d', strtotime($datadodia->data)))
                    <div class="mb-3">
                        <i class="bx bx-calendar-check text-danger"></i>
                        <strong>Dia fechado:</strong>
                        <span>{{ date('d/m/Y', strtotime($datadodia->data)) }}</span>
                    </div>

    <form method="GET" action="{{ route('tela_pedido.abrirDia') }}">
        <input type="hidden" name="id" value="{{ $datadodia->id }}">
        <button type="submit" class="btn btn-success w-100">
            <i class="bx bx-lock-open"></i> ABRIR DIA
        </button>
    </form>
@else
    <div class="mb-3">
        <i class="bx bx-calendar text-muted"></i>
        <strong>Dia fechado:</strong>
        <span class="text-muted">Nenhuma data registrada.</span>
    </div>

    <form method="GET" action="{{ route('tela_pedido.diaFechado') }}">
        <button type="submit" class="btn btn-primary w-100">
            <i class="bx bx-lock"></i> FECHAR DIA
        </button>
    </form>
@endif


            </div>

            </div>
            <hr>
            <h6 class="mb-0 text-uppercase">Pesquisar vendas</h6>
            <form method="GET" action="{{ route('tela_pedido.pedidosDodia') }}" class="mb-3">
                <div class="row mt-3">
                    <div class="col-md-4">
                        <input type="text" name="pesquisa" class="form-control" placeholder="Pesquisar por nome, telefone ou código do pedido" value="{{ request('pesquisa') }}">
                    </div>
                    <div class="col-md-2">
                        <input type="date" name="start_date" class="form-control" value="{{ request('start_date') }}" placeholder="Data inicial">
                    </div>
                    <div class="col-md-2">
                        <input type="date" name="end_date" class="form-control" value="{{ request('end_date') }}" placeholder="Data final">
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary"><i class="bx bx-search"></i> Pesquisar</button>
                        <a id="clear-filter" class="btn btn-danger" href="{{ route('telasPedido.index') }}"><i class="bx bx-eraser"></i> Limpar</a>
                    </div>
                </div>
            </form>
            <hr>
<h4 class="mb-3 text-center text-primary">📅 Pedidos do Dia</h4>
            <div class="row mt-3">
              @foreach($pedidos as $pedido)
    <div class="col-md-4 mb-3">
        <div class="card radius-10 shadow-lg border-0 {{ $pedido->view == 0 ? 'bg-success text-white' : '' }}" id="card-{{ $pedido->id }}">
            <div class="card-body">
                <h6 class="card-title">Pedido #{{ $pedido->codigo_pedido }}</h6>
                <h6 class="card-title">Nome {{ $pedido->nome }}</h6>
                <p class="mb-1"><strong>Data:</strong> {{ \Carbon\Carbon::parse($pedido->data)->format('d/m/Y H:i') }}</p>
                <p class="mb-1"><strong>Forma de Pagamento:</strong> {{ $pedido->forma_pagamento }}</p>
                <p class="mb-1"><strong>Valor Total:</strong> R$ {{ number_format($pedido->total, 2, ',', '.') }}</p>
                <p class="mb-1"><strong>Status:</strong> {{ $pedido->status }}</p>
 <a href="#" class="btn btn-success ms-3 d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#editarPagamentoModal{{ $pedido->id }}" title="Editar Forma de Pagamento">
    ALTERAR FORMA DE PAGAMENTO <i class="bx bx-credit-card text-white"></i>
</a>
           @php
                        // Buscar o pagamento relacionado a este pedido
                        $pagamento = $statusPagamentos->get($pedido->id);
                    @endphp

                   @if ($pagamento)
  @if(!empty($pagamento->status))

        <p class="mb-1"><strong>Status Pagamento:</strong> {{ $pagamento->status }}</p>
        @endif
@if(!empty($pagamento->status) && $pagamento->status != 'aprovado')
    <p class="mb-1">
        <a href="https://deliveryba.com.br/pg/checarpagamento.php?item={{ $pedido->id }}&id_venda={{ $pagamento->id_venda }}" target="_blank" class="btn btn-primary">
            Confirmar Pagamento
        </a>
    </p>
    <p class="mb-1">
        <a href="https://deliveryba.com.br/pg/?item={{ $pedido->id }}" target="_blank" class="btn btn-secondary">
            Receber Pagamento
        </a>
    </p>
@endif


@if(!empty($pagamento->linha))
@if(!empty($pagamento->linha) && $pagamento->status != 'aprovado')
    <textarea id="pixChave" class="form-control" rows="1" readonly style="resize: none;">{{ $pagamento->linha }}</textarea>
@endif
@endif

@else
@endif

<p class="mb-1">
    <strong>OPÇÃO:</strong> 
    {{ $pedido->opcao_delivery === 'true' ? 'DELIVERY' : 'RETIRADA BALCÃO' }}
</p>
  <div class="d-flex justify-content-between align-items-center mt-3 gap-2">
    {{-- Código existente para exibir o status --}}
    <div class="mt-3 button-group-status d-flex flex-wrap gap-2">
        {{-- Botões para alterar o status --}}

        @if ($pedido->status === 'Aberto')
            {{-- Se o pedido está Aberto, só mostra o botão "Confirmar Pedido" --}}
            <form action="{{ route('telasPedido.update', $pedido->id) }}" method="POST">
                @csrf
                @method('PUT')
                <input type="hidden" name="status" value="Em Andamento">
                <input type="hidden" name="empresa_id" value="{{ auth()->check() ? auth()->user()->empresa_id : '' }}">
                <button type="submit" class="btn btn-status-confirmar btn-sm" data-tippy-content="Clique para confirmar o pedido">
                    <i class="fas fa-hourglass-half"></i> Confirmar Pedido
                </button>
            </form>
        @elseif ($pedido->status === 'Em Andamento')
            {{-- Se o pedido está Em Andamento, mostra a opção de entrega/retirada e Finalizar --}}
            @if ($pedido->opcao_delivery === 'true')
                <form action="{{ route('telasPedido.update', $pedido->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="status" value="Saiu para Entrega">
                    <input type="hidden" name="empresa_id" value="{{ auth()->check() ? auth()->user()->empresa_id : '' }}">
                    <button type="submit" class="btn btn-status-entrega btn-sm" data-tippy-content="Clique para alterar para 'Saiu para Entrega'">
                        <i class="fas fa-motorcycle"></i> Saiu para Entrega
                    </button>
                </form>
            @else
                <form action="{{ route('telasPedido.update', $pedido->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="status" value="Disponível para Retirada">
                    <input type="hidden" name="empresa_id" value="{{ auth()->check() ? auth()->user()->empresa_id : '' }}">
                    <button type="submit" class="btn btn-status-retirada btn-sm" data-tippy-content="Clique para alterar para 'Disponível para Retirada'">
                        <i class="fas fa-store"></i> Retirada
                    </button>
                </form>
            @endif
            {{-- Botão Finalizar sempre disponível quando "Em Andamento" --}}
            <form action="{{ route('telasPedido.update', $pedido->id) }}" method="POST">
                @csrf
                @method('PUT')
                <input type="hidden" name="status" value="Finalizado">
                <input type="hidden" name="empresa_id" value="{{ auth()->check() ? auth()->user()->empresa_id : '' }}">
                <button type="submit" class="btn btn-status-finalizar btn-sm" data-tippy-content="Clique para finalizar o pedido">
                    <i class="fas fa-check"></i> Finalizar
                </button>
            </form>
        @elseif ($pedido->status === 'Saiu para Entrega' || $pedido->status === 'Disponível para Retirada')
            {{-- Se o pedido saiu para entrega ou está disponível, mostra o botão "Finalizar" --}}
            <form action="{{ route('telasPedido.update', $pedido->id) }}" method="POST">
                @csrf
                @method('PUT')
                <input type="hidden" name="status" value="Finalizado">
                <input type="hidden" name="empresa_id" value="{{ auth()->check() ? auth()->user()->empresa_id : '' }}">
                <button type="submit" class="btn btn-status-finalizar btn-sm" data-tippy-content="Clique para finalizar o pedido">
                    <i class="fas fa-check"></i> Finalizar
                </button>
            </form>
        @endif

        {{-- Botão Cancelar (visível em todos os status, exceto Finalizado e Cancelado) --}}
        @if ($pedido->status !== 'Finalizado' && $pedido->status !== 'Cancelado')
            <form action="{{ route('telasPedido.update', $pedido->id) }}" method="POST">
                @csrf
                @method('PUT')
                <input type="hidden" name="status" value="Cancelado">
                <input type="hidden" name="empresa_id" value="{{ auth()->check() ? auth()->user()->empresa_id : '' }}">
                <button type="submit" class="btn btn-status-cancelar btn-sm" data-tippy-content="Clique para cancelar o pedido">
                    <i class="fas fa-times"></i> Cancelar
                </button>
            </form>
        @endif
    </div>

    {{-- Script JavaScript para Tippy.js (se usar) --}}
    @push('scripts')
    <script>
        $(document).ready(function() {
            // Inicializa os tooltips (se estiver usando Tippy.js)
            if (typeof tippy !== 'undefined') {
                tippy('[data-tippy-content]');
            }
        });
    </script>
    @endpush
   <div class="mt-3 button-group-status d-flex flex-wrap gap-2">

<form action="{{ route('telasPedido.destroy', $pedido->id) }}" method="POST" id="form-{{ $pedido->id }}">
    @csrf
    @method('DELETE')
    <button type="button" class="btn btn-delete btn-sm btn-danger" onclick="confirmarExclusao({{ $pedido->id }})">
        <i class="bx bx-trash"></i> Excluir
    </button>
</form>

    <button class="btn btn-status-ver btn-sm" data-id="{{ $pedido->id }}" data-bs-toggle="modal" data-bs-target="#modal{{ $pedido->id }}">
        <i class="bx bx-show me-1"></i> Visualizar Pedido
    </button>
</div>
</div>
</div>

            </div>
        </div>@if ($pedido->view === 0)
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            var audio = new Audio('https://deliveryba.com.br/campainha.mp3');

            function tocarSom() {
                audio.play().catch(() => {
                    document.body.addEventListener("click", tocarSom, { once: true });
                });
            }

            // Toca o som imediatamente
            tocarSom();

            // Verifica a condição e toca o som a cada 5 segundos enquanto $pedido->view for 0
            var intervalo = setInterval(function () {
                if ({{ $pedido->view }} === 0) {
                    tocarSom();
                } else {
                    clearInterval(intervalo); // Para de tocar quando a condição não for mais atendida
                }
            }, 5000);
        });
    </script>
@endif


  {{-- Modal para editar Forma de Pagamento --}}
<div class="modal fade" id="editarPagamentoModal{{ $pedido->id }}" tabindex="-1" aria-labelledby="editarPagamentoModalLabel{{ $pedido->id }}" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editarPagamentoModalLabel{{ $pedido->id }}">Alterar Forma de Pagamento do Pedido #{{ $pedido->codigo_pedido }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <form action="{{ route('tela_pedido.forma_pagamento', $pedido->id) }}" method="POST">
                @csrf
                {{-- Se a rota aceitar PUT, use @method('PUT') --}}
                    <input type="hidden" name="pedido_id" value="{{ $pedido->id }}">

                <div class="modal-body">
                    <div class="mb-3">
                        <label for="forma_pagamento{{ $pedido->id }}" class="form-label">Selecione a Forma de Pagamento</label>
                        <select name="forma_pagamento" id="forma_pagamento{{ $pedido->id }}" class="form-select" required>
                            <option value="Dinheiro" {{ $pedido->forma_pagamento == 'Dinheiro' ? 'selected' : '' }}>Dinheiro</option>
                            <option value="Pix" {{ $pedido->forma_pagamento == 'Pix' ? 'selected' : '' }}>Pix</option>
                            <option value="Cartão de Crédito" {{ $pedido->forma_pagamento == 'Cartão de Crédito' ? 'selected' : '' }}>Cartão de Crédito</option>
                            <option value="Cartão de Débito" {{ $pedido->forma_pagamento == 'Cartão de Débito' ? 'selected' : '' }}>Cartão de Débito</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Salvar</button>
                </div>
            </form>
        </div>
    </div>
</div>

        
                    <!-- Modal para Visualizar Pedido -->
                    <div class="modal fade" id="modal{{ $pedido->id }}" tabindex="-1" aria-labelledby="modalLabel{{ $pedido->id }}" aria-hidden="true">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="modalLabel{{ $pedido->id }}">Pedido #{{ $pedido->codigo_pedido }}</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                                </div>
                                <div class="modal-body">
                                   
<style>
    .pedido-item {
        font-family: Arial, sans-serif;
        font-size: 14px;
        line-height: 1.6;
        margin-bottom: 15px;
    }

    .pedido-item p {
        margin: 5px 0;
        padding: 0;
    }

    .pedido-item strong {
        font-weight: bold;
    }

    .pedido-item br {
        margin: 5px 0;
    }
</style>
                                    <div class="container">
                                        <div class="boxed-md">
                                            <b>PEDIDO: #{{ $pedido->codigo_pedido }}</b><br>
                                            <p>Data: {{ \Carbon\Carbon::parse($pedido->data)->format('d/m/Y') }}</p>
                                            <p>Forma de Pagamento: {{ $pedido->forma_pagamento }}</p>
                                            
                                            <div class="line"></div>
                                            <b>DADOS DO CLIENTE:</b><br>
                                            <p><strong>Nome:</strong> {{ $pedido->nome }}</p>
                                            <p><strong>Telefone:</strong> {{ $pedido->telefone }}</p>
                                            <div class="line"></div>
                                             @if(!empty($pedido->rua) || !empty($pedido->unidade) || !empty($pedido->bairro) || !empty($pedido->cidade) || !empty($pedido->uf))
        <b>ENDEREÇO DE ENTREGA:</b><br>  <p><strong>Rua:</strong> {{ $pedido->rua }}{{ $pedido->unidade ? ', ' . $pedido->unidade : '' }}</p>
        <p><strong>Bairro:</strong> {{ $pedido->bairro }}</p>
        <p><strong>Cidade/UF:</strong> {{ $pedido->cidade }}/{{ $pedido->uf }}</p>
        @if(!empty($pedido->complemento))
            <p><strong>Complemento:</strong> {{ $pedido->complemento }}</p>
        @endif
    @else
        <p><strong>Endereço não informado.</strong></p>
    @endif
                                            <b>RESUMO DO PEDIDO:</b><br>
@php
    $texto = strip_tags($pedido->resumo_pedidos);
    
    $texto = preg_replace('/(?!^)(Qtd:)/', '<br>$1', $texto);
    
    $texto = preg_replace('/([^\s])OBS:/', '$1 OBS:', $texto);
@endphp

<p>{!! nl2br($texto) !!}</p>
                                            <div class="line"></div>
                                            <b>PAGAMENTO:</b><br>
                                            <p><strong>Subtotal:</strong> R$ {{ number_format($pedido->sub_total, 2, ',', '.') }}</p>
                                            @if($pedido->desconto > 0)
                                                <p><strong>Desconto:</strong> {{ $pedido->desconto }}%</p>
                                            @endif
                                            @if($pedido->valor_taxa > 0)
                                                <p><strong>Taxa de Delivery:</strong> R$ {{ number_format($pedido->valor_taxa, 2, ',', '.') }}</p>
                                            @endif
                                            <p><strong>Total:</strong> R$ {{ number_format($pedido->total, 2, ',', '.') }}</p>
                                            @if($pedido->valor_troco > 0)
                                                <p><strong>Troco para:</strong> R$ {{ number_format($pedido->valor_troco, 2, ',', '.') }}</p>
                                            @endif
                                            <div class="line"></div>
                                            <p><strong>Observações:</strong> {{ $pedido->observacao }}</p>
                                            <div class="line"></div>
                                            
                                            <p>*** OBRIGADO E BOM APETITE ***</p>
                                        </div>
                                    </div>
                                   
                                </div>
                                <div class="modal-footer">
                             
                    <a href="javascript:void(0);" class="btn btn-primary" onclick="imprimirPedido('pedido_{{ $pedido->codigo_pedido }}')">🖨️ Imprimir </a>

<!-- Div para a impressão -->
<div id="pedido_{{ $pedido->codigo_pedido }}" class="print-area" style="display: none;">
    <div class="boxed-md">
        <div class="titulo">
            <b>PEDIDO: #{{ $pedido->codigo_pedido }}</b><br>
            <p>Data: {{ \Carbon\Carbon::parse($pedido->data)->format('d/m/Y') }}</p>
        </div>
        <div class="linha"></div>
        <div class="cliente">
            <b>DADOS DO CLIENTE:</b><br>
<p><strong>Nome:</strong> {{ urldecode(urldecode($pedido->nome)) }}</p>
            <p><strong>Telefone:</strong> {{ $pedido->telefone }}</p>
        </div>
        <div class="linha"></div>
        <div class="resumo">
            <b>RESUMO DO PEDIDO:</b><br>
@php
    $texto = strip_tags($pedido->resumo_pedidos);
    
    $texto = preg_replace('/(?!^)(Qtd:)/', '<br>$1', $texto);
    
    $texto = preg_replace('/([^\s])OBS:/', '$1 OBS:', $texto);
@endphp

<p>{!! nl2br($texto) !!}</p>        </div>
        <div class="linha"></div>
        <div class="pagamento">
            <b>PAGAMENTO:</b><br>
            <p><strong>Subtotal:</strong> R$ {{ number_format($pedido->sub_total, 2, ',', '.') }}</p>
            @if($pedido->desconto > 0)
                <p><strong>Desconto:</strong> {{ $pedido->desconto }}%</p>
            @endif
            @if($pedido->valor_taxa > 0)
                <p><strong>Taxa de Delivery:</strong> R$ {{ number_format($pedido->valor_taxa, 2, ',', '.') }}</p>
            @endif
            <p><strong>Total:</strong> R$ {{ number_format($pedido->total, 2, ',', '.') }}</p>
            @if($pedido->valor_troco > 0)
                <p><strong>Troco para:</strong> R$ {{ number_format($pedido->valor_troco, 2, ',', '.') }}</p>
            @endif
        </div>
        <div class="linha"></div>
        <p class="mb-1"><strong>Forma de Pagamento:</strong> {{ $pedido->forma_pagamento }}</p>
        <div class="linha"></div>
        <div class="observacoes">
            <p><strong>Observações:</strong> {{ $pedido->observacao }}</p>
        </div>
        <div class="linha"></div>
<div class="endereco">
    <b>ENDEREÇO DE ENTREGA:</b><br>
    <p><strong>Rua:</strong> {{ $pedido->rua }}{{ $pedido->unidade ? ', ' . $pedido->unidade : '' }}</p>
    <p><strong>Bairro:</strong> {{ $pedido->bairro }}</p>
    <p><strong>Cidade/UF:</strong> {{ $pedido->cidade }}/{{ $pedido->uf }}</p>
    @if(!empty($pedido->complemento))
        <p><strong>Complemento:</strong> {{ $pedido->complemento }}</p>
    @endif
  
</div>

        <div class="agradecimento">
<p>🎉 *** OBRIGADO PELA PREFERÊNCIA! *** 🍽️</p>
        </div>       <div class="linha"></div>

    </div>
</div>
<script>
function imprimirPedido(pedidoId) {
    var conteudo = document.getElementById(pedidoId).innerHTML;
    var janela = window.open('', '', 'height=600,width=800');
    
    janela.document.write('<html><head><title>Imprimir Pedido</title>');
    
    // Ajustando estilos para impressão fiscal com fundo de papel
    janela.document.write(`
        <style>
            @page { size: auto; margin: 0; } /* Remove margens e ajusta para impressão */
            body {
                font-family: monospace;
                font-size: 14px; /* Fonte adequada */
                margin: 0;
                padding: 10px;
            }
            .titulo {
                font-size: 18px;
                text-align: center;
                margin-bottom: 10px;
                font-weight: bold;
            }
            .linha {
                border-top: 2px solid #000;
                margin: 14px 0;
            }
            .cliente, .resumo, .pagamento, .observacoes, .agradecimento {
                margin: 14px 0;
            }
            p {
                margin: 6px 0;
                font-size: 14px;
            }
            strong {
                font-size: 16px; /* Tamanho de texto maior para negrito */
            }
        </style>
    `);
    
    janela.document.write('</head><body>');
    janela.document.write(conteudo);
    janela.document.write('</body></html>');
    janela.document.close();
    
    janela.onload = function () { 
        janela.print(); 
        janela.close(); 
    };
}
</script>




                  </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            <div class="d-flex justify-content-center mt-4">
                {{ $pedidos->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
<script>
    setInterval(function() {
        location.reload();
    }, 30000); 
</script>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    function confirmarExclusao(id) {
        Swal.fire({
            title: 'Tem certeza que deseja excluir?',
            text: "Essa ação é irreversível!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Sim, excluir',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('form-' + id).submit();
            }
        });
    }
</script>
<script>
    document.getElementById("copiarLink").addEventListener("click", function(event) {
        event.preventDefault();  // Impede a navegação do link

        // Cria um elemento temporário para copiar o link
        var tempInput = document.createElement("input");
        tempInput.value = this.href;  // Pega o link do atributo href
        document.body.appendChild(tempInput);
        tempInput.select();
        document.execCommand("copy");  // Copia o conteúdo para a área de transferência
        document.body.removeChild(tempInput);

        alert("Link copiado: " + tempInput.value);  // Alerta que o link foi copiado
    });
</script>
