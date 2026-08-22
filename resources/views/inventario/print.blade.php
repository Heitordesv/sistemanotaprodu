<!DOCTYPE html>
<html>
<head>
	<title></title>
	<!--  -->

	<style type="text/css">

		.content{
			margin-top: -30px;
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
		th{
			font-size: 13px;
		}

	</style>

</head>
<body>
	<div class="content">
		@if($config->logo != "")
		<table>
			<tr>
				<td class="" style="width: 150px;">
					<img src="{{'data:image/png;base64,' . base64_encode(file_get_contents(@public_path('logos/').$config->logo))}}" width="100px;">
				</td>

				<td class="" style="width: 550px;">
					<center><label class="titulo">INVENTÁRIO DE ESTOQUE</label></center>
				</td>
			</tr>
		</table>
		@else
		<center><label class="titulo">INVENTÁRIO DE ESTOQUE</label></center>
		@endif
	</div>
	<br>
	<table>
		<tr>
			<td class="" style="width: 700px;">
				<strong>Dados do Emitente</strong>
			</td>
		</tr>
	</table>
	<table>
		<tr>
			<td class="b-top" style="width: 500px;">
				Razão social: <strong>{{$config->razao_social}}</strong>
			</td>
			<td class="b-top" style="width: 197px;">
				CNPJ: <strong>{{$config->cnpj}}</strong>
			</td>
		</tr>
	</table>
	<table>
		<tr>
			<td class="b-top" style="width: 700px;">
				Endereço: <strong>{{$config->logradouro}}, {{$config->numero}} - {{$config->bairro}} - {{$config->municipio}} ({{$config->UF}})</strong>
			</td>
		</tr>
	</table>
	<table>
		<tr>
			<td class="b-bottom b-top" style="width: 700px;">
				Telefone: <strong>{{$config->fone}}</strong>
			</td>
		</tr>
	</table>

	<table>
		<tr>
			<td class="b-bottom" style="width: 250px;">
				Tipo: <strong>{{$inventario->tipo}}</strong>
			</td>

			<td class="b-bottom" style="width: 221px;">
				Data início: <strong>{{\Carbon\Carbon::parse($inventario->inicio)->format('d/m/Y')}}</strong>
			</td>
			<td class="b-bottom" style="width: 221px;">
				Data término: <strong>{{\Carbon\Carbon::parse($inventario->fim)->format('d/m/Y')}}</strong>
			</td>
		</tr>
	</table>

	<table>
		<tr>
			<td class="" style="width: 350px;">
				Nº Doc: <strong>{{$inventario->id}}</strong>
			</td>
			<td class="" style="width: 347px;">

			</td>
		</tr>
	</table>



	<table>
		<tr>
			<td class="b-top b-bottom" style="width: 700px; height: 50px;">
				<strong>PRODUTOS:</strong>
			</td>
		</tr>
	</table>	


	<table>
		<thead>
			<tr>
				<td class="" style="width: 70px;">
					Codigo
				</td>
				<td class="" style="width: 255px;">
					Descrição
				</td>
				<td class="" style="width: 70px;">
					Unid.
				</td>
				<td class="" style="width: 70px;">
					Quant.
				</td>
				<td class="" style="width: 70px;">
					Vl Custo
				</td>
				<td class="" style="width: 70px;">
					Vl Venda
				</td>
				<td class="" style="width: 70px;">
					Total
				</td>
			</tr>
		</thead>
		
		<tbody>
			@foreach($itens as $i)
			<tr>
				<th class="b-top">
					{{$i->produto->id}}
					@if($i->produto->refenrecia != "")
					/{{$i->produto->refenrecia}}
					@endif
				</th>
				<th class="b-top">
					{{$i->produto->nome}}
					{{$i->produto->grade ? " (" . $i->produto->str_grade . ")" : ""}}
					@if($i->produto->lote != "")
					| Lote: {{$i->produto->lote}}, 
					Vencimento: {{$i->produto->vencimento}}
					@endif
				</th class="b-top">
				<th class="b-top">{{$i->produto->unidade_venda}}</th>
				<th class="b-top">{{$i->quantidade}}</th>
				<th class="b-top">{{number_format($i->produto->valor_compra, $casasDecimais, ',', '.')}}</th>
				<th class="b-top">{{number_format($i->produto->valor_venda, $casasDecimais, ',', '.')}}</th>
				<th class="b-top">{{number_format($i->quantidade * $i->produto->valor_venda, $casasDecimais, ',', '.')}}</th>

			</tr>

			@if($i->observacao!= "")
			<tr>
				<th colspan="7">
					Observação: {{$i->observacao}}
				</th>
			</tr>
			@endif
			@endforeach
		</tbody>
	</table>
	<br>

	<table>
		<tr>
			<td class="b-top" style="width: 700px;">
			</td>

		</tr>
	</table>

	
	
	<table>
		<tr>
			<td class="" style="width: 350px;">
				Data do documento: <strong>{{date('d/m/Y H:i:s')}}</strong>
			</td>
			<td class="" style="width: 347px;">

			</td>
		</tr>
	</table>

	@if($inventario->observacao != "")
	<table>
		<tr>
			<td class="" style="width: 700px;">
				<strong>Observação: 
					{{$inventario->observacao}}
				</strong>
			</td>
		</tr>
	</table>
	@endif

	<table>
		<tr>
			<td class="" style="width: 350px;">
				Total em estoque de compra:
				<strong> 
					{{number_format($totaliza['compra'], 2, ',', '.')}}
				</strong>
			</td>

			<td class="" style="width: 350px;">
				Total em estoque de venda:
				<strong> 
					{{number_format($totaliza['venda'], 2, ',', '.')}}
				</strong>
			</td>
		</tr>
	</table>
	<table>
		<tr>
			<td class="" style="width: 350px;">
				Quantidade de itens:
				<strong> 
					{{$totaliza['qtd']}}
				</strong>
			</td>

			<td class="" style="width: 350px;">
				Total de linhas:
				<strong> 
					{{sizeof($itens)}}
				</strong>
			</td>
			
		</tr>
	</table>
</body>
</html>