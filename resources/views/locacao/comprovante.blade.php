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

		.align-right{
			float: right;
		}

	</style>

</head>
<body>
	
	<br>
	<table>
		<tr>
			<td class="" style="width: 250px; height: 170px;">
				@if($config->logo != null)

				@php
				$public = env('SERVIDOR_WEB') ? 'public/' : '';

				@endphp

				<img src="{{'data:image/png;base64,' . base64_encode(file_get_contents(@$public.'logos/'.$config->logo))}}" width="250px;" height="150px;">
				@endif
			</td>

			<td class="" style="width: 450px; height: 150px">
				<br>
				<strong style="font-size: 12px; float: right">{{$config->nome_fantasia}}</strong><br>

				<strong style="font-size: 12px; float: right;">Ordem de Serviço</strong><br>
				<strong style="font-size: 12px; float: right;">_______________________________________________________</strong><br>
				<b style="font-size: 10px; float: right;">
					{{$config->razao_social}}
				</b><br>
				<b style="font-size: 10px; float: right; margin-top: -7px;">
					CNPJ: <strong>{{str_replace(" ", "", $config->cnpj)}} - IE: {{$config->ie}}</strong>
				</b><br>
				<b class="line-control" style="font-size: 10px; float: right; margin-top: -15px;"> {{$config->fone}} - {{$config->email}}</b>
				<b class="line-control" style="font-size: 10px; float: right; margin-top: -4px;">
					{{$config->logradouro}}, {{$config->numero}} - {{$config->bairro}}
				</b><br>
				<b class="line-control" style="font-size: 10px; float: right; margin-top: -13px;">
					{{$config->municipio}} ({{$config->UF}}) - {{$config->cep}}
				</b>


			</td>
		</tr>
	</table>

	<table>
		<tr>
			<td style="width: 700px;">
				<hr>
			</td>
		</tr>
	</table>

	<table>
		<tr>
			<td class="" style="width: 450px;">
				<span style="font-size: 12px;">Histórico</span>
			</td>

			<td class="" style="width: 250px;">
				<span style="font-size: 18px;">Número: <strong>{{$locacao->id}}</strong></span>
			</td>
		</tr>
	</table>

	<table>
		<tr>
			<td class="" style="width: 450px;">
				<span style="font-size: 18px;">Cliente: <strong>{{$locacao->cliente->razao_social}}</strong></span>
			</td>

			<td class="" style="width: 250px;">
				<span style="font-size: 18px;">Data: <strong>{{ \Carbon\Carbon::parse($locacao->inicio)->format('d/m/y') }}</strong></span>
			</td>
		</tr>
	</table>

	<table>
		<tr>
			<td class="" style="width: 450px;">
				<span style="font-size: 15px;">Endereço: {{$locacao->cliente->rua}}, {{$locacao->cliente->numero}} - {{$locacao->cliente->bairro}} - {{$locacao->cliente->cidade->nome}} ({{$locacao->cliente->cidade->uf}})</span>
			</td>

			<td class="" style="width: 250px;">
				<span style="font-size: 18px;">Situação: 
					<strong>
						@if($locacao->status == 0)
						Em andamento
						@else
						Concluido
						@endif
					</strong>
				</span><br>
				<span style="font-size: 18px;">Prev. de Conclusão:: 
					<strong>

						@if($locacao->fim != '1969-12-31')
						{{ \Carbon\Carbon::parse($locacao->fim)->format('d/m/Y')}}
						@else
						--
						@endif
					</strong>
				</span>
			</td>
		</tr>
	</table>

	<table>
		<tr>
			<td class="" style="width: 233px;">
				<span style="font-size: 15px;">CPF/CNPJ: {{$locacao->cliente->cpf_cnpj}}</span>
			</td>

			<td class="" style="width: 233px;">
				<span style="font-size: 15px;">RG/Inscricao Estadual: {{$locacao->cliente->ie_rg}}</span>
			</td>
			
		</tr>
	</table>

	<table>
		<tr>
			
			<td class="" style="width: 233px;">
				<span style="font-size: 15px;">Telefone: {{$locacao->cliente->telefone}}</span>
			</td>

			<td class="" style="width: 233px;">
				<span style="font-size: 15px;">Celular: {{$locacao->cliente->celular}}</span>
			</td>
			
		</tr>
	</table>

	<br>

	<table>
		<tr>
			<td class="b-top b-bottom" style="width: 700px; height: 50px;">
				Produtos e Serviços:
			</td>
		</tr>
	</table>	


	<table>
		<thead>
			<tr>
				<td class="" style="width: 100px;">
					Cód Barras
				</td>
				<td class="" style="width: 170px;">
					Descrição
				</td>
				<td class="" style="width: 70px;">
					Unidade
				</td>
				<td class="" style="width: 70px;">
					Valor
				</td>
				<td class="" style="width: 200px;">
					Observação
				</td>
				<td class="" style="width: 70px;">
					Total
				</td>

			</tr>
		</thead>

		<tbody>
			@foreach($locacao->itens as $key => $i)
			<tr @if($key%2 != 0) style="background-color: #e8eaf6" @endif>
				<th style="text-align: left;" @if($key == 0) class="b-top" @endif>{{ $i->produto->codBarras != "SEM GTIN" ? $i->produto->codBarras : '--' }}</th>
				<th style="text-align: left;" @if($key == 0) class="b-top" @endif>{{ $i->produto->nome }}</th>
				<th style="text-align: left;" @if($key == 0) class="b-top" @endif>{{ $i->produto->unidade_venda }}</th>
				<th style="text-align: left;" @if($key == 0) class="b-top" @endif>{{ number_format($i->valor, 2, ',', '.') }}</th>

				<th style="text-align: left;" @if($key == 0) class="b-top" @endif>{{ $i->observacao != "" ? $i->observacao : '--' }}</th>

				<th style="text-align: left;" @if($key == 0) class="b-top" @endif>{{ number_format($i->valor, 2, ',', '.') }}</th>

			</tr>

			@endforeach
		</tbody>
	</table>


	<table>
		<tr>

			<td class="b-top" style="width: 450px;">
			</td>
			<td class="b-top" style="width: 150px;">
				<strong style="float: right; margin-right: 10px;">Total de produtos: 
				</strong>
			</td>
			<td class="b-top" style="width: 100px;">
				<strong style="margin-left: 20px;"> 
					{{number_format($locacao->total, 2, ',', '.')}}
				</strong>
			</td>

		</tr>
	</table>


	<table>
		<tr>

			<td class="b-top b-bottom" style="width: 700px;">
				Observação: 
				<strong>{{ $locacao->observacao != "" ? $locacao->observacao : '--' }}
				</strong>

			</td>
		</tr>
	</table>
	<br><br>
	<table>
		<tr>

			<td class="" style="width: 700px;">

				_______________________________________
				<br>
				Assinatura
			</td>
		</tr>
	</table>

</body>
</html>