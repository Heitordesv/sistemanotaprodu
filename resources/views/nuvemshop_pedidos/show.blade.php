@extends('default.layout',['title' => 'Detalhes do Pedido'])
@section('content')
<div class="page-content">
	<div class="invoice overflow-auto">
		<div style="min-width: 600px">
			<header>
				<div class="row">
					<div class="col">
						
					</div>
					
				</div>
			</header>
			<main>
				<div class="row contacts">
					<div class="col invoice-to">
						<div class="text-gray-light">Transação ID Nuvem Shop: <strong class="text-succcess">{{$pedido->pedido_id}}</strong></div>
						<h2 class="to">DETALHES DO PEDIDO</h2>
						<div class="text-gray-light"></div>
						<div class="address">
							{{$pedido->cliente->rua}}, {{$pedido->cliente->numero}} - {{$pedido->cliente->bairro}} - {{$pedido->cliente->complemento}} {{$pedido->cliente->cidade->nome}} ({{$pedido->cliente->cidade->uf}}) | {{$pedido->cliente->cep}}
						</div>
					</div>
					<div class="col invoice-details">
						<h1 class="invoice-id">{{ $pedido->nome }}</h1>
						<div class="date">DATA: {{ $pedido->getDate() }}</div>

					</div>
				</div>

				@foreach($erros as $e)
				<h5 class="text-danger">{{$e}}</h5>
				@endforeach

				<a target="_blank" class="btn btn-warning mb-2" href="{{ route('clientes.edit', $pedido->cliente->id) }}">
					<i class="bx bx-edit"></i>Editar cliente
				</a>
				<table>
					<thead>
						<tr>
							<th class="text-left">#</th>
							<th class="text-left">Produto</th>
							<th class="text-right">Quantidade</th>
							<th class="text-right">Valor unitário</th>
							<th class="text-right">Total</th>

						</tr>
					</thead>
					<tbody>

						@php $subTotal = 0; @endphp
						@foreach($pedido->itens as $i)
						<tr>
							<td class="no">01</td>
							<td class="text-left">
								{{ $i->produto->nome }} 
								@if($i->produto->grade)
								({{ $i->produto->str_grade }})
								@endif
							</td>
							<td class="qty">{{ $i->quantidade }}</td>
							<td class="unit">R${{ __moeda($i->valor) }}</td>
							<td class="total">R${{ __moeda($i->quantidade*$i->valor) }}</td>
						</tr>
						@php $subTotal = $i->quantidade*$i->valor; @endphp
						@endforeach


					</tbody>
					<tfoot>
						<tr>
							<td colspan="2"></td>
							<td colspan="2">SUBTOTAL</td>
							<td>R$ {{ __moeda($subTotal) }}</td>
						</tr>
						@if($pedido->desconto > 0)
						<tr>
							<td colspan="2"></td>
							<td colspan="2">Desconto</td>
							<td>R$ {{ __moeda($pedido->desconto) }}</td>
						</tr>
						@endif
						<tr>
							<td colspan="2"></td>
							<td colspan="2">TOTAL</td>
							<td>R$ {{ __moeda($pedido->total) }}</td>
						</tr>
					</tfoot>
				</table>

				<div class="notices">
					<div>Observação:</div>
					<div class="notice">{{ $pedido->observacao }}</div>
				</div>

				<table class="mt-4">
					<thead>
						<tr>

							<th class="font-weight-bold text-muted text-uppercase">PAGAMENTO STATUS</th>
							<th class="font-weight-bold text-muted text-uppercase">ENVIO STATUS</th>
							<th class="font-weight-bold text-muted text-uppercase text-right">TOTAL</th>
						</tr>
					</thead>
					<tbody>
						<tr class="font-weight-bolder">

							<td>{{$pedido->status_pagamento}}</td>
							<td>{{$pedido->status_envio}}</td>
							<td class="text-primary font-size-h3 font-weight-boldest text-right">R$ {{ number_format($pedido->total, 2, ',', '.')}}</td>
						</tr>
					</tbody>
				</table>
			</main>

			@if(!$pedido->venda)
			<a class="btn btn-info" href="{{ route('nuvemshop-pedidos.nfe', $pedido->id) }}">
				<i class="la la-file"></i>
				Gerar NFe
			</a>
			@else

			@if($pedido->numero_nfe > 0)
			<a class="btn btn-success" target="_blank" href="{{ route('nfe.imprimir', [$pedido->venda->id])}}">
				<i class="la la-print"></i>
				Imprimir Danfe
			</a>
			@endif
			<a class="btn btn-info" href="{{ route('vendas.show', [$pedido->venda->id]) }}">
				<i class="la la-file-alt"></i>
				Ver Venda
			</a>

			@endif

			<a target="_blank" class="btn btn-primary" href="{{ route('nuvemshop-pedidos.print', $pedido->id) }}">
				<i class="la la-print"></i>
				Imprimir Pedido
			</a>
		</div>

		<div></div>
	</div>
</div>
@endsection