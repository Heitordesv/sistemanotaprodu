@extends('default.layout', ['title' => 'Pedidos'])

@section('content')
<meta name="csrf-token" content="{{ csrf_token() }}">

<div class="page-content">
    <div class="card">
        <div class="card-body p-4">
            <div class="page-breadcrumb d-sm-flex align-items-center mb-3">
                <div class="ms-auto">
                 
                </div>
            </div>
            <hr>
            <h6 class="mb-0 text-uppercase">Pesquisar vendas</h6>
            <form method="GET" action="{{ route('telasPedido.index') }}" class="mb-3">
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
<h2 class="text-center mt-3">📌 Pedidos na Cozinha</h2>
<p class="text-muted text-center">🍽️ Acompanhe os pedidos que estão sendo preparados.</p>
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
                
               <div class="d-flex justify-content-between align-items-center mt-3 gap-2">
    <!-- Botão Editar -->
    <a href="#" class="btn btn-warning text-white btn-sm d-flex align-items-center" data-bs-toggle="modal" data-bs-target="#editarModal{{ $pedido->id }}">
        <i class="bx bx-edit me-1"></i> Editar
    </a>

    <!-- Botão Excluir com Confirmação -->
    <form action="{{ route('telasPedido.destroy', $pedido->id) }}" method="post" id="form-{{$pedido->id}}" class="m-0">
        @csrf
        @method('delete')
        <button type="button" class="btn btn-danger btn-sm d-flex align-items-center btn-delete">
            <i class="bx bx-trash me-1"></i> Excluir
        </button>
    </form>

    <!-- Botão Ver -->
    <button class="btn btn-info btn-sm d-flex align-items-center btn-ver" data-id="{{ $pedido->id }}" data-bs-toggle="modal" data-bs-target="#modal{{ $pedido->id }}">
        <i class="bx bx-show me-1"></i> Ver
    </button>
</div>

            </div>
        </div>@if($pedido->view == 0)
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            var audio = new Audio('https://deliveryba.com.br/campainha.mp3');

            // Função para tentar tocar o áudio
            function tocarSom() {
                audio.play().catch(() => {
                    // Se o autoplay falhar, aguarda interação do usuário
                    document.body.addEventListener("click", tocarSom, { once: true });
                });
            }

            tocarSom(); // Executa ao carregar a página
        });
    </script>
@endif

                </div>



<!-- Modal -->
<div class="modal fade" id="editarModal{{ $pedido->id }}" tabindex="-1" aria-labelledby="editarModalLabel{{ $pedido->id }}" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="editarModalLabel{{ $pedido->id }}">Alterar Status do Pedido</h5>
                                            <h5 class="modal-title" id="modalLabel{{ $pedido->id }}"> #{{ $pedido->codigo_pedido }}</h5>

        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form action="{{ route('telasPedido.update', $pedido->id) }}" method="POST">
          @csrf
          @method('PUT')
          <div class="mb-3">
              
            <label for="status" class="form-label">Selecione o Status</label>
           <select name="status" id="status" class="form-select" required>
    <option value="Aberto" {{ $pedido->status == 'Aberto' ? 'selected' : '' }}>Aberto</option>
    <option value="Em Andamento" {{ $pedido->status == 'Em Andamento' ? 'selected' : '' }}>Em Andamento</option>
    <option value="Saiu para Entrega" {{ $pedido->status == 'Saiu para Entrega' ? 'selected' : '' }}>Saiu para Entrega</option>
    <option value="Disponível para Retirada" {{ $pedido->status == 'Disponível para Retirada' ? 'selected' : '' }}>Disponível para Retirada</option>
    <option value="Finalizado" {{ $pedido->status == 'Finalizado' ? 'selected' : '' }}>Finalizado</option>
    <option value="Cancelado" {{ $pedido->status == 'Cancelado' ? 'selected' : '' }}>Cancelado</option>
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
                                            <b>RESUMO DO PEDIDO:</b><br>
                                            <p>{{ nl2br(strip_tags($pedido->resumo_pedidos)) }}</p>
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
            <p>{{ nl2br(strip_tags($pedido->resumo_pedidos)) }}</p>
        </div>
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
        <div class="agradecimento">
            <p>*** OBRIGADO E BOM APETITE ***</p>
        </div>
    </div>
</div>
<script>
   function imprimirPedido(pedidoId) {
        var conteudo = document.getElementById(pedidoId).innerHTML;
        
        // Cria uma nova janela para impressão
        var janela = window.open('', '', 'height=600,width=800');
        janela.document.write('<html><head><title>Imprimir Pedido</title>');
        
        // Ajustando estilos para impressão fiscal com fundo de papel
        janela.document.write(`
            <style>
                body {
                    font-family: Arial, sans-serif;
                    font-size: 18px; /* Aumenta a fonte geral */
                    margin: 0;
                    padding: 20px;
                    line-height: 1.6;
                    background-color: #f5f5f5; /* Cor do papel (cinza claro) */
                }
                .titulo {
                    font-size: 22px; /* Título maior */
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
                    font-size: 18px; /* Aumenta fonte do texto */
                }
                strong {
                    font-size: 20px; /* Negrito maior */
                }
            </style>
        `);

        janela.document.write('</head><body>');
        janela.document.write(conteudo);
        janela.document.write('</body></html>');
        
        // Imprime a nova janela
        janela.document.close();
        janela.print();
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

