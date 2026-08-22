@extends('default.layout',['title' => 'Gerar Venda do Pedido'])
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

						<div class="address">
							{{$pedido->cliente->rua}}, {{$pedido->cliente->numero}} - {{$pedido->cliente->bairro}} - {{$pedido->cliente->complemento}} {{$pedido->cliente->cidade->nome}} ({{$pedido->cliente->cidade->uf}}) | {{$pedido->cliente->cep}}
						</div>
					</div>
					<div class="col invoice-details">
						<h1 class="invoice-id">{{ $pedido->nome }}</h1>
						<div class="date">DATA: {{ $pedido->getDate() }}</div>

					</div>
				</div>

				{!!Form::open()
				->put()
				->route('nuvemshop-pedidos.store-venda', [$pedido->id])!!}
				<div class="pl-lg-4">
					<div class="row g-3">
						<div class="col-md-3">
							{!!Form::select('forma_pagamento', 'Forma de pagamento', ['' => 'selecione'] + App\Models\Venda::tiposPagamento())
							->required()
							->attrs(['class' => 'form-select'])
							!!}
						</div>

						<div class="col-md-3">
							{!!Form::select('natureza', 'Natureza de operação', ['' => 'selecione'] + $naturezas->pluck('natureza', 'id')->all())
							->required()
							->attrs(['class' => 'form-select'])
							!!}
						</div>

						<div class="col-md-3">
							{!!Form::select('transportadora', 'Transportadora', ['' => 'selecione'] + $transportadoras->pluck('razao_social', 'id')->all())
							->attrs(['class' => 'form-select'])
							!!}
						</div>
						<div class="col-md-3">
							{!! Form::select('tipo', 'Tipo do frete', [
							0 => 'Emitente',
							1 => 'Destinatário',
							2 => 'Terceiros',
							3 => 'Sem Frete',
							])->attrs(['class' => 'form-select'])->required() !!}
						</div>
						
						<div class="col-md-2">
							{!!Form::text('valor_frete', 'Valor do frete')
							->value()->attrs(['class' => 'moeda'])
							!!}
						</div>
						<div class="col-md-2">
							{!!Form::text('placa', 'Placa veículo')
							->value()->attrs(['class' => 'placa'])
							!!}
						</div>
						<div class="col-md-2">
							{!!Form::select('uf_placa', 'UF', ['' => 'selecione'] + \App\Models\Cidade::estados())
							->attrs(['class' => 'form-select'])
							!!}
						</div>

						<div class="col-md-2">
							{!! Form::text('especie', 'Espécie')->attrs(['class' => '']) !!}
						</div>

						<div class="col-md-2">
							{!! Form::text('numeracao_volumes', 'N. de volumes')->attrs(['class' => '']) !!}
						</div>

						<div class="col-md-2">
							{!! Form::text('qtd_volumes', 'Qtd. volumes')->attrs(['class' => '']) !!}
						</div>

						<div class="col-md-2">
							{!! Form::text('peso_liquido', 'Peso liquido')->attrs(['class' => '']) !!}
						</div>

						<div class="col-md-2">
							{!! Form::text('peso_bruto', 'Peso bruto')->attrs(['class' => '']) !!}
						</div>

						<div class="col-12">
							@if(sizeof($erros) == 0)
							<button class="btn btn-success">
								<i class="la la-check"></i>
								Salvar
							</button>

							@else
							@foreach($erros as $e)
							<p>
								<div class="alert alert-danger border-0 bg-danger alert-dismissible fade show">
									<div class="text-white">{{ $e }}</div>
								</div>
							</p>
							@endforeach
							@endif
						</div>
					</div>

				</div>
				{!!Form::close()!!}

			</main>

		</div>

		<div></div>
	</div>
</div>
@endsection