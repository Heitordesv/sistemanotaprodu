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
		.page_break { page-break-before: always; }
	</style>

</head>
<body>
	@php
		$logoPath = !empty($config->logo)
			? public_path('uploads/configEmitente/') . $config->logo
			: null;
		$usarLogoEmpresa = $logoPath && is_file($logoPath);
		$logoPadraoPath = public_path('logos/logonfenotas.png');
	@endphp

	<div class="content">
		<table>
			<tr>

				@if($usarLogoEmpresa)
				<td class="" style="width: 150px;">
					<img src="{{'data:image/png;base64,' . base64_encode(file_get_contents($logoPath))}}" style="max-width: 120px; max-height: 70px;">
				</td>
				@else
				<td class="" style="width: 150px;">
					<img src="{{'data:image/png;base64,' . base64_encode(file_get_contents($logoPadraoPath))}}" style="max-width: 120px; max-height: 70px;">
				</td>
				@endif

				<td class="" style="width: 400px;">
					<center><label class="titulo">ORÇAMENTO</label></center>
				</td>
			</tr>
		</table>

	</div>
	<br>
	<table>
		<tr>
			<td class="" style="width: 700px;">
				<strong>Dados da empresa</strong>
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
			<td class="b-top b-bottom" style="width: 300px;">
				Complemento: <strong>{{$config->complemento}}</strong>
			</td>
			<td class="b-top b-bottom" style="width: 200px;">
				CEP: <strong>{{$config->cep}}</strong>
			</td>
			<td class="b-top b-bottom" style="width: 200px;">
				Telefone: <strong>{{$config->fone}}</strong>
			</td>
		</tr>
	</table>
	<table>
		<tr>
			<td class="b-bottom" style="width: 700px;">
				Email: <strong>{{$config->email}}</strong>
			</td>
			
		</tr>
	</table>
	<br>
	<table>
		<tr>
			<td class="" style="width: 700px;">
				<strong>Dados do cliente</strong>
			</td>
		</tr>
	</table>
	<table>
		<tr>
			<td class="b-top" style="width: 450px;">
				Nome: <strong>{{$item->cliente->razao_social}}</strong>
			</td>
			<td class="b-top" style="width: 247px;">
				CPF/CNPJ: <strong>{{$item->cliente->cpf_cnpj}}</strong>
			</td>
		</tr>
	</table>
	<table>
		<tr>
			<td class="b-top" style="width: 500px;">
				Endereço: <strong>{{$item->cliente->rua}}, {{$item->cliente->numero}} - {{$item->cliente->bairro}} - {{$item->cliente->cidade->nome}} ({{$item->cliente->cidade->uf}})</strong>
			</td>

			<td class="b-top" style="width: 200px;">
				CEP: <strong>{{$item->cliente->cep}}</strong>
			</td>
		</tr>
	</table>

	<table>
		<tr>
			<td class="b-top b-bottom" style="width: 300px;">
				Complemento: <strong>{{$item->cliente->complemento }}</strong>
			</td>

			<td class="b-top b-bottom" style="width: 200px;">
				Telefone: <strong>{{$item->cliente->telefone}}</strong>
			</td>
			<td class="b-top b-bottom" style="width: 200px;">
				Celular: <strong>{{$item->cliente->celular}}</strong>
			</td>
		</tr>
	</table>
	<table>
		<tr>
			<td class="b-bottom" style="width: 700px;">
				Email: <strong>{{$item->cliente->email}}</strong>
			</td>
			
		</tr>
	</table>

	<table>
		<tr>
			<td class="" style="width: 350px;">
				Nº Doc: <strong>{{$item->numero_sequencial}}</strong>
			</td>
			
		</tr>
	</table>
	<table>
		<tr>
			<td class="b-top b-bottom" style="width: 700px; height: 50px;">
				<strong>MERCADORIAS:</strong>
			</td>
		</tr>
	</table>	


	<table>
		<thead>
			<tr>
				<td class="" style="width: 72px;">
					
				</td>
				<td class="" style="width: 60px;">
					#
				</td>
				<td class="" style="width: 350px;">
					Descrição
				</td>
				<td class="" style="width: 70px;">
					Quant.
				</td>
				<td class="" style="width: 50px;">
					Vl Uni
				</td>
				<td class="" style="width: 80px;">
					Vl Liq.
				</td>
			</tr>
		</thead>
		@php
		$somaItens = 0;
		$somaTotalItens = 0;
		$tipoDimensao = false;
		$tipoReceita = false;
		@endphp
		<tbody>
			@foreach($item->itens as $i)
			<tr>
				<th class="b-top">
				
				</th>
				<th class="b-top">{{$i->produto->id}}</th>
				<th class="b-top">
					{{$i->produto->nome}}
					{{$i->produto->grade ? " (" . $i->produto->str_grade . ")" : ""}}

					@if($i->produto->lote != "")
					| Lote: {{$i->produto->lote}}, 
					Vencimento: {{$i->produto->vencimento}}
					@endif

				</th class="b-top">
				<th class="b-top">{{number_format($i->quantidade, 2, ',', '.')}}</th>
				<th class="b-top">{{number_format($i->valor, $casasDecimais, ',', '.')}}</th>
				<th class="b-top">{{number_format($i->quantidade * $i->valor, $casasDecimais, ',', '.')}}</th>

			</tr>
			@php
			$somaItens += $i->quantidade;
			$somaTotalItens += $i->quantidade * $i->valor;
			if($i->altura > 0 || $i->esquerda > 0){
				$tipoDimensao = true;
			}

			if($i->produto->receita){
				$tipoReceita = true;
			}
			@endphp

			@endforeach
		</tbody>
	</table>
	<br>

	<table>
		<tr>
			<td class="b-top b-bottom" style="width: 350px;">
				<center><strong>Quantidade Total: {{$somaItens}}</strong></center>
			</td>

			<td class="b-top b-bottom" style="width: 350px;">
				<center><strong>Valor Total dos Itens: 
					{{number_format($somaTotalItens, 2, ',', '.')}}
				</strong></center>
			</td>
		</tr>
	</table>

	@if($item->duplicatas()->exists())
	<table>
		<tr>
			<td class="b-bottom" style="width: 700px; height: 50px;">
				<strong>FATURA:</strong>
			</td>
		</tr>
	</table>
	<table>
		<tr>
			<td class="b-bottom" style="width: 150px;">
				Vencimento
			</td>
			<td class="b-bottom" style="width: 150px;">
				Valor
			</td>
		</tr>
		@foreach($item->duplicatas as $key => $d)
		<tr>

			<td class="b-bottom">
				<strong>{{ \Carbon\Carbon::parse($d->vencimento)->format('d/m/Y')}}</strong>
			</td>
			<td class="b-bottom">
				<strong>{{number_format($d->valor, 2, ',', '.')}}</strong>
			</td>

		</tr>
		@endforeach
	</table>
	@endif

	<br>
	<table>
		<tr>
			<td class="" style="width: 700px;">
				Forma de pagamento: <strong> 
					{{$item->forma_pagamento == 'a_vista' ? 'À vista' : $item->forma_pagamento}}
					@if($item->getFormaPagamento() != null)
					- <span style="color: #8950FC">{{ $item->getFormaPagamento()->infos }}</span>
					@endif
				</strong>
			</td>
		</tr>
	</table>
	<table>
		<tr>
			@if(!$item->vendedor_id)
			<td class="" style="width: 350px;">
				Vendedor: <strong>{{$item->usuario->nome}}</strong>
			</td>
			@endif
			<td class="" style="width: 350px;">
				Frete por conta: <strong>
					@if($item->frete)
					@if($item->frete->tipo == 0)
					Emitente
					@elseif($item->frete->tipo == 1)
					Destinatário
					@elseif($item->frete->tipo == 2)
					Terceiros
					@else
					Outros
					@endif
					@else
					sem frete
					@endif
				</strong>
			</td>
		</tr>
	</table>

	<table>
		<tr>
			@if($item->vendedor_id)
			<td class="" style="width: 250px;">
				Vendedor: <strong>{{ $item->vendedor_setado->funcionario->nome }}</strong>
			</td>
			@endif
			<td class="" style="width: 250px;">
				Data do orçamento: <strong>{{\Carbon\Carbon::parse($item->created_at)->format('d/m/Y H:i')}}</strong>
			</td>
			<td class="" style="width: 200px;">
				@if($item->data_entrega != null)
				Data da entrega: <strong>{{\Carbon\Carbon::parse($item->data_entrega)->format('d/m/Y')}}</strong>
				@endif
			</td>
		</tr>
	</table>

	<table>
		<tr>
			<td class="" style="width: 170px;">
				Desconto (-):
				<strong> 
					{{number_format($item->desconto, 2, ',', '.')}}
				</strong>
			</td>

			<td class="" style="width: 170px;">
				Acréscimo (+):
				<strong> 
					{{number_format($item->acrescimo, 2, ',', '.')}}
				</strong>
			</td>

			<td class="" style="width: 170px;">
				Frete (+):
				<strong> 
					@if($item->frete)
					{{number_format($item->frete->valor, 2, ',', '.')}}
					@else
					0,00
					@endif
				</strong>
			</td>

			<td class="" style="width: 200px;">
				Valor Líquido:
				<strong> 
					{{number_format($item->valor_total - $item->desconto + $item->acrescimo, $casasDecimais, ',', '.')}}
				</strong>
			</td>

		</tr>
	</table>


	@if($item->observacao != "" || $config->campo_obs_pedido != "")
	<table>
		<tr>
			<td class="" style="width: 700px;">
				<span>Observação: 
					<strong>{{$config->campo_obs_pedido}}
						{{$item->observacao}}
					</strong>
				</span>
			</td>
		</tr>
	</table>
	@endif

	<br><br><br>
	<table>
		<tr>
			<td class="" style="width: 350px;">
				<strong>
					________________________________________
				</strong><br>
				<span style="font-size: 11px;">{{$config->razao_social}}</span>

			</td>

			<td class="" style="width: 350px;">
				<strong>
					________________________________________
				</strong><br>
				<span style="font-size: 11px;">{{$item->cliente->razao_social}}</span>
			</td>
		</tr>
	</table>

	@if($config->validade_orcamento > 0)
	<br>
	<br>
	<table>
		<tr>
			<td class="" style="width: 700px;">
				<strong>Esse orçamento tem validade de {{$config->validade_orcamento }} Dias.</strong>
			</td>
		</tr>
	</table>
	@endif

	@if($tipoDimensao)
	<div class="page_break"></div>
	<table>
		<tr>
			<td class="" style="width: 700px;">
				<strong>Dados do cliente</strong>
			</td>
		</tr>
	</table>
	<table>
		<tr>
			<td class="b-top" style="width: 450px;">
				Nome: <strong>{{$item->cliente->razao_social}}</strong>
			</td>
			<td class="b-top" style="width: 247px;">
				CPF/CNPJ: <strong>{{$item->cliente->cpf_cnpj}}</strong>
			</td>
		</tr>
	</table>
	<table>
		<tr>
			<td class="b-top" style="width: 500px;">
				Endereço: <strong>{{$item->cliente->rua}}, {{$item->cliente->numero}} - {{$item->cliente->bairro}} - {{$item->cliente->cidade->nome}} ({{$item->cliente->cidade->uf}})</strong>
			</td>

			<td class="b-top" style="width: 200px;">
				Telefone: <strong>{{$item->cliente->telefone}}</strong>
			</td>
		</tr>
	</table>

	<table>
		<tr>
			<td class="b-top" style="width: 350px;">
				Nº Doc: <strong>{{$item->id}}</strong>
			</td>
			<td class="b-top" style="width: 347px;">

			</td>
		</tr>
	</table>

	<table>
		<tr>
			<td class="b-top b-bottom" style="width: 700px; height: 50px;">
				<strong>MERCADORIAS:</strong>
			</td>
		</tr>
	</table>	

	<table>
		<thead>
			<tr>
				<td class="" style="width: 70px;">
					Cod
				</td>
				<td class="" style="width: 470px;">
					Descrição
				</td>
				<td class="" style="width: 70px;">
					Qtd. Dim.
				</td>
				<td class="" style="width: 70px;">
					Qtd. 
				</td>
			</tr>
		</thead>

		@php
		$somaItens = 0;
		$somaTotalItens = 0;
		$tipoDimensao = false;
		@endphp
		<tbody>
			@foreach($item->itens as $i)
			<tr>
				<th class="b-top">{{$i->produto->id}}</th>
				<th class="b-top">
					{{$i->produto->nome}}
					{{$i->produto->grade ? " (" . $i->produto->str_grade . ")" : ""}}
					@if($i->produto->lote != "")
					| Lote: {{$i->produto->lote}}, 
					Vencimento: {{$i->produto->vencimento}}
					@endif
					@if($i->produto->tipo_dimensao != '')
					@if($i->produto->tipo_dimensao == 'area')
					[Altura: {{$i->altura}}, Largura: {{$i->largura}}, Profundidade: {{$i->profundidade}}]
					@else
					[Superior: {{$i->superior}}, Infeior: {{$i->inferior}}, Esquerda: {{$i->esquerda}}, Direita: {{$i->direita}}]

					@endif
					@endif
				</th class="b-top">
				<th class="b-top">{{number_format($i->quantidade, 2, ',', '.')}}</th>
				<th class="b-top">{{number_format($i->quantidade_dimensao, 2, ',', '.')}}</th>

			</tr>
			@php
			$somaItens += $i->quantidade;
			$somaTotalItens += $i->quantidade * $i->valor;
			if($i->altura > 0 || $i->esquerda > 0){
				$tipoDimensao = true;
			}
			@endphp

			@endforeach
		</tbody>
	</table>
	<br>


	<br>
	<table>
		<tr>
			<td class="" style="width: 200px;">
				<strong>Vendedor: 
					{{$item->usuario->nome}}
				</strong>
			</td>
			<td class="" style="width: 250px;">
				Data da orcamento: <strong>{{\Carbon\Carbon::parse($item->created_at)->format('d/m/Y H:i')}}</strong>
			</td>
			<td class="" style="width: 250px;">
				Data da entrega: <strong>{{\Carbon\Carbon::parse($item->data_entrega)->format('d/m/Y')}}</strong>
			</td>
		</tr>
	</table>

	@if($item->observacao != "")
	<table>
		<tr>
			<td class="" style="width: 700px;">
				<strong>Observação: 
					{{$item->observacao}}
				</strong>
			</td>
		</tr>
	</table>
	@endif
	@endif

	@if($tipoReceita)

	<div class="page_break"></div>
	<table>
		<tr>

			@if($usarLogoEmpresa)
			<td class="" style="width: 150px;">
				<img src="{{'data:image/png;base64,' . base64_encode(file_get_contents($logoPath))}}" style="max-width: 120px; max-height: 70px;">
			</td>
			@else
			<td class="" style="width: 150px;">
				<img src="{{'data:image/png;base64,' . base64_encode(file_get_contents($logoPadraoPath))}}" style="max-width: 120px; max-height: 70px;">
			</td>
			@endif
		</tr>
	</table>
	<table>
		<tr>
			<td class="" style="width: 700px;">
				<strong>Dados do cliente</strong>
			</td>
		</tr>
	</table>
	<table>
		<tr>
			<td class="b-top" style="width: 450px;">
				Nome: <strong>{{$item->cliente->razao_social}}</strong>
			</td>
			<td class="b-top" style="width: 247px;">
				CPF/CNPJ: <strong>{{$item->cliente->cpf_cnpj}}</strong>
			</td>
		</tr>
	</table>
	<table>
		<tr>
			<td class="b-top" style="width: 500px;">
				Endereço: <strong>{{$item->cliente->rua}}, {{$item->cliente->numero}} - {{$item->cliente->bairro}} - {{$item->cliente->cidade->nome}} ({{$item->cliente->cidade->uf}})</strong>
			</td>

			<td class="b-top" style="width: 200px;">
				Telefone: <strong>{{$item->cliente->telefone}}</strong>
			</td>
		</tr>
	</table>

	<table>
		<tr>
			<td class="b-top" style="width: 350px;">
				Nº Doc: <strong>{{$item->id}}</strong>
			</td>
			<td class="b-top" style="width: 347px;">

			</td>
		</tr>
	</table>

	<table>
		<tr>
			<td class="b-top b-bottom" style="width: 700px; height: 50px;">
				<strong>Produtos:</strong>
			</td>
		</tr>
	</table>
	<!-- receitas -->
	<table>
		<thead>
			<tr>
				<td class="" style="width: 70px;">
					Cod
				</td>
				<td class="" style="width: 540px;">
					Descrição
				</td>

				<td class="" style="width: 14px;">
					Qtd. 
				</td>
			</tr>
		</thead>

		@php
		$somaItens = 0;
		$somaTotalItens = 0;
		$tipoDimensao = false;

		@endphp
		<tbody>
			@foreach($item->itens as $i)
			<tr>
				<th class="b-top">{{$i->produto->id}}</th>
				<th class="b-top">
					{{$i->produto->nome}}
					{{$i->produto->grade ? " (" . $i->produto->str_grade . ")" : ""}}
					@if($i->produto->lote != "")
					| Lote: {{$i->produto->lote}}, 
					Vencimento: {{$i->produto->vencimento}}
					@endif

				</th class="b-top">
				<th class="b-top">{{number_format($i->quantidade, 2, ',', '.')}}</th>

			</tr>
			@php
			$somaItens += $i->quantidade;
			$somaTotalItens += $i->quantidade * $i->valor;

			@endphp

			<tr>
				<td colspan="3">Composição do item:</td>
			</tr>
			@foreach($i->produto->receita->itens as $ir)
			<tr>
				<th style="text-align: left;" class="b-bottom" colspan="2">{{$ir->produto->nome}}</th>
				<th class="b-bottom">{{$ir->quantidade}} {{$ir->medida}}</th>
			</tr>
			@endforeach
			@endforeach
		</tbody>
	</table>
	<br>

	<h4>Informação tecnica do(s) produto(s):</h4>
	@foreach($item->itens as $i)
	@if($i->produto->info_tecnica_composto != "")
	<p><strong>{{$i->produto->nome}}: </strong> {{$i->produto->info_tecnica_composto}}</p>
	@endif
	@endforeach

	@endif

</body>
</html>