<!DOCTYPE html>
<html>
<head>
	<title></title>
	<link rel="stylesheet" href="https://unpkg.com/purecss@1.0.1/build/pure-min.css" integrity="sha384-oAOxQR6DkCoMliIh8yFnu25d7Eq/PHS21PClpwjOTeU2jRSq11vu66rf90/cZr47" crossorigin="anonymous">
	<link rel="stylesheet" href="/css/materialize.min.css">

</head>
<body>

	<div class="row">
		<div class="col s12">
			<h3 class="center-align">Cotação Referência <strong style="color: red">{{$cotacao->referencia}}</strong></h3>
			<h3 class="center-align">Fornecedor: <strong>{{$fornecedor->razao_social}}</strong></h3>
			<h3 class="center-align">CPF/CNPJ: <strong>{{$fornecedor->cpf_cnpj}}</strong></h3>
			
		</div>

		<table class="pure-table">
			<thead>
				<tr>
					<th width="200">ITEM</th>
					<th width="80">VALOR UNIT</th>
					<th width="80">QUANTIDADE</th>
					<th width="80">SUB TOTAL</th>
				</tr>
			</thead>

			

			<tbody>
			    <?php $soma = 0; ?>
				@foreach($itens as $key => $i)
				<tr class="@if($key%2 == 0) pure-table-odd @endif">
					<td>{{$i['item']}}</td>
					<td>{{number_format($i['valor_unitario'], 2, ',', '.')}}</td>
					<td>{{number_format($i['quantidade'], 2, ',', '.')}}</td>
					<td>{{number_format($i['valor_total'], 2, ',', '.')}}</td>

					<?php $soma += $i['valor_total']; ?>
				</tr>
				@endforeach
			</tbody>
		</table>

		<h3 class="center-align">Total: <strong style="color: red">R$ {{number_format($soma, 2, ',', '.')}}</strong></h3>

	</div>
	<div class="row">
		<canvas id="grafico-vendas" style="width: 100%; margin-left: 100px; margin-top: 20px;"></canvas>
	</div>

</body>
</html>