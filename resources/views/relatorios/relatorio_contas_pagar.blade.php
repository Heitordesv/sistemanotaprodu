<html>
<head>

	<style type="text/css">

		@page {
			margin: 0cm 0cm;
		}

		/** Define now the real margins of every page in the PDF **/
		body {
			margin-top: 2cm;
			margin-left: 1cm;
			margin-right: 1cm;
			margin-bottom: 2cm;
		}

		/** Define the header rules **/
		header {
			position: fixed;
			margin-top: 25px;
			margin-left: 40px;
			margin-right: 40px;
			margin-bottom: 25px;
			height: 20px;
		}
		.banner {
			text-align: center;
			display: flex;
			align-items: flex-start;
		}

		p{
			font-size: 12px;
			margin-top: 0px;
			margin-bottom: 2px;
		}

		.pure-table-odd{
			background: #EBEBEB;
		}

		.logoBanner img {
			float: left;
			max-width: 70px;
			margin-top: 0;
		}

		.banner h1 {
			position: absolute;
			margin-top: 0;
		}

		.banner hr {
			margin-top: 29px;
			margin-left: 120px;
		}

		.date {
			float: right;
		}

		.provider {
			text-align: left;
			margin-top: 5px;
			margin-bottom: 10px;
		}


		.client {
			margin-bottom: 0.6rem;
		}

		footer {
			position: fixed;
			bottom: 0.5cm;
			left: 0cm;
			right: 0cm;
			height: 1.5cm;
		}

		img {
			max-width: 100px;
			height: auto;
		}


		table {
			font-size: 0.8rem;
			margin: 0;
		}

		table thead {
			border-bottom: 1px solid rgb(206, 206, 206);
			border-top: 1px solid rgb(206, 206, 206);
		}

		.caption {
			/* Make the caption a block so it occupies its own line. */
			display: block;
		}

		.row {
			display: -webkit-box;
			display: -ms-flexbox;
			display: flex;
			-ms-flex-wrap: wrap;
			flex-wrap: wrap;
			margin-right: -15px;
			margin-left: -15px;
		}

		.col-1, .col-2, .col-3, .col-4, .col-5, .col-6, .col-7, .col-8, .col-9, .col-10, .col-11, .col-12, .col,
		.col-auto, .col-sm-1, .col-sm-2, .col-sm-3, .col-sm-4, .col-sm-5, .col-sm-6, .col-sm-7, .col-sm-8, .col-sm-9, .col-sm-10, .col-sm-11, .col-sm-12, .col-sm,
		.col-sm-auto, .col-md-1, .col-md-2, .col-md-3, .col-md-4, .col-md-5, .col-md-6, .col-md-7, .col-md-8, .col-md-9, .col-md-10, .col-md-11, .col-md-12, .col-md,
		.col-md-auto, .col-lg-1, .col-lg-2, .col-lg-3, .col-lg-4, .col-lg-5, .col-lg-6, .col-lg-7, .col-lg-8, .col-lg-9, .col-lg-10, .col-lg-11, .col-lg-12, .col-lg,
		.col-lg-auto, .col-xl-1, .col-xl-2, .col-xl-3, .col-xl-4, .col-xl-5, .col-xl-6, .col-xl-7, .col-xl-8, .col-xl-9, .col-xl-10, .col-xl-11, .col-xl-12, .col-xl,
		.col-xl-auto {
			position: relative;
			width: 100%;
			min-height: 1px;
			padding-right: 15px;
			padding-left: 15px;
		}

		.col {
			-ms-flex-preferred-size: 0;
			flex-basis: 0;
			-webkit-box-flex: 1;
			-ms-flex-positive: 1;
			flex-grow: 1;
			max-width: 100%;
		}

		.col-auto {
			-webkit-box-flex: 0;
			-ms-flex: 0 0 auto;
			flex: 0 0 auto;
			width: auto;
			max-width: none;
		}

		.col-1 {
			-webkit-box-flex: 0;
			-ms-flex: 0 0 8.333333%;
			flex: 0 0 8.333333%;
			max-width: 8.333333%;
		}

		.col-2 {
			-webkit-box-flex: 0;
			-ms-flex: 0 0 16.666667%;
			flex: 0 0 16.666667%;
			max-width: 16.666667%;
		}

		.col-3 {
			-webkit-box-flex: 0;
			-ms-flex: 0 0 25%;
			flex: 0 0 25%;
			max-width: 25%;
		}

		.col-4 {
			-webkit-box-flex: 0;
			-ms-flex: 0 0 33.333333%;
			flex: 0 0 33.333333%;
			max-width: 33.333333%;
		}

		.col-5 {
			-webkit-box-flex: 0;
			-ms-flex: 0 0 41.666667%;
			flex: 0 0 41.666667%;
			max-width: 41.666667%;
		}

		.col-6 {
			-webkit-box-flex: 0;
			-ms-flex: 0 0 50%;
			flex: 0 0 50%;
			max-width: 50%;
		}

		.col-7 {
			-webkit-box-flex: 0;
			-ms-flex: 0 0 58.333333%;
			flex: 0 0 58.333333%;
			max-width: 58.333333%;
		}

		.col-8 {
			-webkit-box-flex: 0;
			-ms-flex: 0 0 66.666667%;
			flex: 0 0 66.666667%;
			max-width: 66.666667%;
		}

		.col-9 {
			-webkit-box-flex: 0;
			-ms-flex: 0 0 75%;
			flex: 0 0 75%;
			max-width: 75%;
		}

		.col-10 {
			-webkit-box-flex: 0;
			-ms-flex: 0 0 83.333333%;
			flex: 0 0 83.333333%;
			max-width: 83.333333%;
		}

		.col-11 {
			-webkit-box-flex: 0;
			-ms-flex: 0 0 91.666667%;
			flex: 0 0 91.666667%;
			max-width: 91.666667%;
		}

		.col-12 {
			-webkit-box-flex: 0;
			-ms-flex: 0 0 100%;
			flex: 0 0 100%;
			max-width: 100%;
		}
		.text-justify {
			text-align: justify !important;
		}

		.text-nowrap {
			white-space: nowrap !important;
		}

		.text-truncate {
			overflow: hidden;
			text-overflow: ellipsis;
			white-space: nowrap;
		}

		.text-left {
			text-align: left !important;
		}

		.text-right {
			text-align: right !important;
		}

		.text-center {
			text-align: center !important;
		}
		.w-25 {
			width: 25% !important;
		}

		.w-50 {
			width: 50% !important;
		}

		.w-75 {
			width: 75% !important;
		}

		.w-100 {
			width: 100% !important;
		}

		.h-25 {
			height: 25% !important;
		}

		.h-50 {
			height: 50% !important;
		}

		.h-75 {
			height: 75% !important;
		}

		.h-100 {
			height: 100% !important;
		}

		.mw-100 {
			max-width: 100% !important;
		}

		.mh-100 {
			max-height: 100% !important;
		}

		.m-0 {
			margin: 0 !important;
		}

		.mt-0,
		.my-0 {
			margin-top: 0 !important;
		}

		.mr-0,
		.mx-0 {
			margin-right: 0 !important;
		}

		.mb-0,
		.my-0 {
			margin-bottom: 0 !important;
		}

		.ml-0,
		.mx-0 {
			margin-left: 0 !important;
		}

		.m-1 {
			margin: 0.25rem !important;
		}

		.mt-1,
		.my-1 {
			margin-top: 0.25rem !important;
		}

		.mr-1,
		.mx-1 {
			margin-right: 0.25rem !important;
		}

		.mb-1,
		.my-1 {
			margin-bottom: 0.25rem !important;
		}

		.ml-1,
		.mx-1 {
			margin-left: 0.25rem !important;
		}

		.m-2 {
			margin: 0.5rem !important;
		}

		.mt-2,
		.my-2 {
			margin-top: 0.5rem !important;
		}

		.mr-2,
		.mx-2 {
			margin-right: 0.5rem !important;
		}

		.mb-2,
		.my-2 {
			margin-bottom: 0.5rem !important;
		}

		.ml-2,
		.mx-2 {
			margin-left: 0.5rem !important;
		}

		.m-3 {
			margin: 1rem !important;
		}

		.mt-3,
		.my-3 {
			margin-top: 1rem !important;
		}

		.mr-3,
		.mx-3 {
			margin-right: 1rem !important;
		}

		.mb-3,
		.my-3 {
			margin-bottom: 1rem !important;
		}

		.ml-3,
		.mx-3 {
			margin-left: 1rem !important;
		}

		.m-4 {
			margin: 1.5rem !important;
		}

		.mt-4,
		.my-4 {
			margin-top: 1.5rem !important;
		}

		.mr-4,
		.mx-4 {
			margin-right: 1.5rem !important;
		}

		.mb-4,
		.my-4 {
			margin-bottom: 1.5rem !important;
		}

		.ml-4,
		.mx-4 {
			margin-left: 1.5rem !important;
		}

		.m-5 {
			margin: 3rem !important;
		}

		.mt-5,
		.my-5 {
			margin-top: 3rem !important;
		}

		.mr-5,
		.mx-5 {
			margin-right: 3rem !important;
		}

		.mb-5,
		.my-5 {
			margin-bottom: 3rem !important;
		}

		.ml-5,
		.mx-5 {
			margin-left: 3rem !important;
		}

		.p-0 {
			padding: 0 !important;
		}

		.pt-0,
		.py-0 {
			padding-top: 0 !important;
		}

		.pr-0,
		.px-0 {
			padding-right: 0 !important;
		}

		.pb-0,
		.py-0 {
			padding-bottom: 0 !important;
		}

		.pl-0,
		.px-0 {
			padding-left: 0 !important;
		}

		.p-1 {
			padding: 0.25rem !important;
		}

		.pt-1,
		.py-1 {
			padding-top: 0.25rem !important;
		}

		.pr-1,
		.px-1 {
			padding-right: 0.25rem !important;
		}

		.pb-1,
		.py-1 {
			padding-bottom: 0.25rem !important;
		}

		.pl-1,
		.px-1 {
			padding-left: 0.25rem !important;
		}

		.p-2 {
			padding: 0.5rem !important;
		}

		.pt-2,
		.py-2 {
			padding-top: 0.5rem !important;
		}

		.pr-2,
		.px-2 {
			padding-right: 0.5rem !important;
		}

		.pb-2,
		.py-2 {
			padding-bottom: 0.5rem !important;
		}

		.pl-2,
		.px-2 {
			padding-left: 0.5rem !important;
		}

		.p-3 {
			padding: 1rem !important;
		}

		.pt-3,
		.py-3 {
			padding-top: 1rem !important;
		}

		.pr-3,
		.px-3 {
			padding-right: 1rem !important;
		}

		.pb-3,
		.py-3 {
			padding-bottom: 1rem !important;
		}

		.pl-3,
		.px-3 {
			padding-left: 1rem !important;
		}

		.p-4 {
			padding: 1.5rem !important;
		}

		.pt-4,
		.py-4 {
			padding-top: 1.5rem !important;
		}

		.pr-4,
		.px-4 {
			padding-right: 1.5rem !important;
		}

		.pb-4,
		.py-4 {
			padding-bottom: 1.5rem !important;
		}

		.pl-4,
		.px-4 {
			padding-left: 1.5rem !important;
		}

		.p-5 {
			padding: 3rem !important;
		}

		.pt-5,
		.py-5 {
			padding-top: 3rem !important;
		}

		.pr-5,
		.px-5 {
			padding-right: 3rem !important;
		}

		.pb-5,
		.py-5 {
			padding-bottom: 3rem !important;
		}

		.pl-5,
		.px-5 {
			padding-left: 3rem !important;
		}

		.m-auto {
			margin: auto !important;
		}

		.mt-auto,
		.my-auto {
			margin-top: auto !important;
		}

		.mr-auto,
		.mx-auto {
			margin-right: auto !important;
		}

		.mb-auto,
		.my-auto {
			margin-bottom: auto !important;
		}

		.ml-auto,
		.mx-auto {
			margin-left: auto !important;
		}
		.text-success{
			color: #25D366;
		}
		.text-danger{
			color: #F64E60;
		}
	</style>
</head>
<header>
	<div class="headReport" style="display:flex;justify-content:  padding-top:1rem">

		@php $config = App\Models\ConfigNota::configStatic(); @endphp
		@if($config->logo != "")
		<img src="{{'data:image/png;base64,' . base64_encode(file_get_contents(@public_path('logos/').$config->logo))}}" alt="Logo" class="mb-2">
		@else
		<img src="{{'data:image/png;base64,' . base64_encode(file_get_contents(@public_path('imgs/slym.png')))}}" alt="Logo" class="mb-2">

		@endif


		<div class="row text-right">
			<div class="col-12" style="margin-top:0.4rem">
				<small class="float-right" style="color:grey;">Emissão:
				{{ date('d/m/Y - H:i') }}</small><br>
			</div>
		</div>

		<div class="row">
			<h4 style="text-align:center; margin-top: 0px">Relatório de Contas a Pagar</h4>
		</div>
		
	</div>
</header>
<body>
	<br>
	<table class="table-sm table-borderless"
	style="border-bottom: 1px solid rgb(206, 206, 206); margin-bottom:10px;  width: 100%;">
	@if($data_inicial && $data_final)
	<caption>Período: {{$data_inicial}} - {{$data_final}}</caption>
	@endif
	<thead>
		<tr>
			<th width="10%" class="text-left">Referência</th>
			<th width="25%" class="text-left">Fornecedor</th>
			<th width="15%" class="text-left">Data de cadastro</th>
			<th width="15%" class="text-left">Data de vencimento</th>
			<th width="10%" class="text-left">Estado</th>
			<th width="10%" class="text-left">Valor integral</th>
			<th width="10%" class="text-left">Valor pago</th>
			<th width="15%" class="text-left">Tipo de pag.</th>
		</tr>
	</thead>
	<tbody>
		@php 
		$dTemp = null; 
		$somaPago = $somaLinhaPendente = 0; 
		$somaPendente = $somaLinhaPendente = 0; 
		@endphp

		@foreach($contas as $key => $c)

		@if($dTemp != $c->data_vencimento)
		@php 
		$somaLinhaPago = 0; 
		$somaLinhaPendente = 0; 
		@endphp
		<tr style="font-size:15px; background: #D7D7D7;">
			<td colspan="8">Vencimento: {{ \Carbon\Carbon::parse($c->data_vencimento)->format('d/m/Y') }}</td>
		</tr>
		@endif

		<tr style="font-size:12px;">
			<td class="text-left">{{ $c->referencia }}</td>
			<td class="text-left">
				@if($c->fornecedor_id != null)
				{{$c->fornecedor->razao_social}}
				@elseif($c->compra_id != null)
				{{$c->compra->fornecedor->razao_social}}
				@else
				{{ $c->referencia }}
				@endif
			</td>
			<td class="text-left">{{ \Carbon\Carbon::parse($c->created_at)->format('d/m/Y') }}</td>
			<td class="text-left">{{ \Carbon\Carbon::parse($c->data_vencimento)->format('d/m/Y') }}</td>
			<td class="text-left">
				@if($c->status)
				<span class="text-success">Pago</span>
				@else
				<span class="text-danger">Pendente</span>
				@endif
			</td>
			<td class="text-left">{{ number_format($c->valor_integral, 2, ',', '.') }}</td>
			<td class="text-left">{{ number_format($c->valor_pago, 2, ',', '.') }}</td>
			<td class="text-left">{{ $c->tipo_pagamento != '' ? $c->tipo_pagamento : '-' }}</td>
		</tr>

		@php
		if($c->status){
			$somaPago += $c->valor_pago;
			$somaLinhaPago += $c->valor_pago;
		}else{
			$somaPendente += $c->valor_integral;
			$somaLinhaPendente += $c->valor_integral;
		}
		@endphp

		@isset($contas[$key+1])
		@if($contas[$key+1]->data_vencimento != $c->data_vencimento)
		<tr style="font-size:14px; background: #e8f0fe;">
			<td colspan="3">
				Contas Pagas: <strong>R$ {{number_format($somaLinhaPago, 2, ',', '.')}}</strong>
			</td>
			<td colspan="5">
				Contas Pendentes: <strong>R$ {{number_format($somaLinhaPendente, 2, ',', '.')}}</strong>
			</td>
		</tr>
		@php
		$somaLinhaPago = 0; 
		$somaLinhaPendente = 0;
		@endphp
		@endif
		@else

		<tr style="font-size:14px; background: #e8f0fe;">
			<td colspan="3">
				Contas Pagas: <strong>R$ {{number_format($somaLinhaPago, 2, ',', '.')}}</strong>
			</td>
			<td colspan="5">
				Contas Pendentes: <strong>R$ {{number_format($somaLinhaPendente, 2, ',', '.')}}</strong>
			</td>
		</tr>
		@endif

		@php
		$dTemp = $c->data_vencimento;
		@endphp
		@endforeach
	</tbody>
	<tfoot>
		<tr style="font-size:14px;">
			<td colspan="3">
				Total Contas Pagas: <strong>R$ {{number_format($somaPago, 2, ',', '.')}}</strong>
			</td>
			<td colspan="5">
				Total Contas Pendentes: <strong>R$ {{number_format($somaPendente, 2, ',', '.')}}</strong>
			</td>
		</tr>
	</tfoot>
</table>
</body>
<footer id="footer_imagem">
	<hr>
	<table style="width: 100%;">
		<tbody>
			<tr>
				<td class="text-left ml-3 mb-3">
					{{env('SITE_SUPORTE')}}
				</td>
				<td class="text-right">
					@if($config->logo != "")
					<img src="{{'data:image/png;base64,' . base64_encode(file_get_contents(@public_path('logos/').$config->logo))}}" alt="logo" class="mr-3">
					@else
					<img src="{{'data:image/png;base64,' . base64_encode(file_get_contents(@public_path('imgs/slym.png')))}}" alt="Logo" class="mr-3">
					@endif
				</td>
			</tr>
		</tbody>
	</table>
</footer>
</html>
