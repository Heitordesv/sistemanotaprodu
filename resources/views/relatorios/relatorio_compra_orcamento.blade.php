<!DOCTYPE html>
<html>
<head>
	<title></title>
	<style type="text/css">

		.content{
			/*margin-top: -30px;*/
		}
		.titulo{
			font-size: 20px;
			margin-bottom: 0px;
			font-weight: bold;
		}

		.b-top{
			border-top: 1px solid #000; 
		}
		.b-bottom{
			border-bottom: 1px solid #000; 
		}

	</style>
</head>
<body>
	<div class="content">

		<center><label class="titulo">RELATÓRIO DE COMPRAS ORÇAMENTO</label></center>
		<center><label class="titulo">NÃO É DOCUMENTO FISCAL</label></center>
		<center><label class="titulo">NÃO COMPROVA PAGAMENTO</label></center>
		<center><label class="titulo">
			@if($data_inicial && $data_final)
			<h4>Período: <strong style="color: blue">{{$data_inicial}} - {{$data_final}}</strong></h4>
			@endif
		</label></center>

	</div>
	<br>

	<table>
		<thead>
			<tr>
				<td class="" style="width: 95px;">
					Código
				</td>
				<td class="" style="width: 350px;">
					Produto
				</td>
				<td class="" style="width: 80px;">
					Quantidade
				</td>
				
			</tr>
		</thead>
		<tbody>
			@foreach($itens as $key => $i)
			<tr class="@if($key%2 == 0) pure-table-odd @endif">
				<td>{{$i['codigo']}}</td>
				<td>{{$i['produto']}}</td>
				<td>{{number_format($i['quantidade'], 2, ',', '.')}}</td>
			</tr>
			@endforeach
		</tbody>
	</table>
	
</body>
</html>