<!DOCTYPE html>
<html>
<head>
	<title></title>
</head>
<body>
	<h2>Olá, {{$pedido->cliente->nome}}</h2>

	<h3>Recebemos seu pedido obrigado por comprar conosco :)</h3>
	<h5>Forma de pagamento: <strong>{{$pedido->forma_pagamento}}</strong></h5>
	
	<table>
		<thead>
			<tr>
				<th>Produto</th>
				<th>Qtd</th>
				<th>Valor Unit.</th>
				<th>Subtotal</th>
			</tr>
		</thead>
		<tbody>
			@foreach($pedido->itens as $i)
			<tr>
				<td>{{$i->produto->produto->nome}}
					@if($i->produto->produto->grade)
					({{$i->produto->produto->str_grade}})
					@endif
				</td>
				<td>{{$i->quantidade}}</td>
				<td>R$ {{ number_format($i->produto->valor, 2, ',', '.') }}</td>
				<td>R$ {{ number_format($i->quantidade*$i->produto->valor, 2, ',', '.') }}</td>
			</tr>
			@endforeach
		</tbody>
	</table>
	<h5>Atenciosamente, {{$config->nome}}</h5>
</body>
</html>