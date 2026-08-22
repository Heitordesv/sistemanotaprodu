@extends('default.layout',['title' => 'Emitir NFe Entrada'])
@section('content')

@section('css')
<style type="text/css">
	input[type="file"] {
		display: none;
	}

	.file label{
		padding: 10px 10px;
		width: 100%;
		background-color: #212529;
		color: #FFF;
		text-transform: uppercase;
		text-align: center;
		display: block;
		margin-top: 15px;
		cursor: pointer;
		border-radius: 5px;
	}

	.btn{
		width: 250px;
	}

</style>
@endsection

<div class="page-content">
	<div class="card border-top border-0 border-4 border-primary">
		<div class="card-body p-5">
			<div class="page-breadcrumb d-sm-flex align-items-center mb-3">
				<div class="ms-auto">
					<a href="{{ route('compras.index')}}" type="button" class="btn btn-light btn-sm">
						<i class="bx bx-arrow-back"></i> Voltar
					</a>
				</div>
			</div>
			<div class="card-title d-flex align-items-center">
				<h5 class="mb-0 text-primary">Emitir NFe de Entrada</h5>
			</div>

			<div class="card">
				<div class="card-body">
					<div class="row">
						<h5>Fornecedor: <strong class="text-primary">{{ $item->fornecedor->razao_social }}</strong></h5>
						<h6>CNPJ: <strong class="text-success">{{ $item->fornecedor->cpf_cnpj }}</strong></h6>
						<h6>Data: <strong class="text-success">{{ __data_pt($item->created_at, true) }}</strong></h6>
						<h6>Valor Total: <strong class="text-success">{{ number_format($item->valor_total, 2, ',', '.') }}</strong></h6>
						<h6>Cidade: <strong class="text-success">{{ $item->fornecedor->cidade->nome }} ({{ $item->fornecedor->cidade->uf }})</strong></h6>
						<h6>Chave NFe: <strong class="text-success">{{$item->chave != "" ? $item->chave : '--'}}</strong></h6>
						<h6>Estado: {!! $item->estadoEmissao() !!}</h6>
						<h6>Número: <strong class="text-success">{{ $item->numero_emissao }}</strong></h6>
					</div>
				</div>
			</div>

			<div class="card">
				<div class="card-header">
					<h4>Itens da compra</h4>
				</div>
				<div class="card-body">
					<div class="table-responsive">
						<table class="table mb-0 table-striped">

							<thead>
								<tr>
									<th>#</th>
									<th>Produto</th>
									<th>Quantidade</th>
									<th>Valor unitário</th>
									<th>sub total</th>
								</tr>
							</thead>
							<tbody>
								@foreach($item->itens as $i)
								<tr>
									<td>{{ $i->id }}</td>
									<td>{{ $i->produto->nome }}</td>
									<td>{{ $i->quantidade }}</td>
									<td>{{ __moeda($i->valor_unitario) }}</td>
									<td>{{ __moeda($i->valor_unitario*$i->quantidade) }}</td>
								</tr>
								@endforeach

							</tbody>
							<tfoot>
								<tr>
									<th colspan="4">Soma</th>
									<th>{{ __moeda($item->total) }}</th>
								</tr>
							</tfoot>
						</table>

					</div>
				</div>
			</div>
			
			@if($item->estado == 'novo' || $item->estado == 'rejeitado')

			<div class="card">
				<div class="card-body">

					{!!Form::open()
					->put()
					->route('compras.set-natureza', [$item->id])
					->multipart()!!}
					<div class="pl-lg-4">

						<div class="row g-3">
							<div class="col-md-3">
								{!!Form::select('natureza_id', 'Natureza', ['' => 'Selecione'] + $naturezas->pluck('natureza', 'id')->all())
								->attrs(['class' => 'select2'])
								->value($item->natureza_id)
								->required()
								!!}
							</div>

							<div class="col-md-3">
								{!!Form::select('tipo_pagamento', 'Tipo de pagamento', App\Models\Compra::tiposPagamento())
								->attrs(['class' => 'select2'])
								->required()
								->value($item->tipo_pagamento)

								!!}
							</div>

							<div class="col-12">
								<button type="submit" class="btn btn-danger px-5">Salvar</button>
							</div>
						</div>

					</div>
					{!!Form::close()!!}
				</div>
			</div>
			@endif

			@if($item->natureza_id != null)
			<div class="card">
				<div class="card-body">
					@if($item->estado == 'novo' || $item->estado == 'rejeitado')
					<a target="_blank" href="{{ route('compras.danfe-temp', [$item->id]) }}" class="btn btn-info">Visualizar DANFE</a>
					<a href="{{ route('compras.xml-temp', [$item->id]) }}" target="_blank" class="btn btn-dark">Visualizar XML</a>
					<button id="btn-enviar" type="button" class="btn btn-success">Emitir NFe</button>
					@endif

					@if($item->estado == 'aprovado')
					<a target="_blank" href="{{ route('compras.imprimir-danfe', [$item->id]) }}" class="btn btn-info">Imprimir</a>

					<button id="btn-consultar" type="button" class="btn btn-primary">Consultar</button>
					<button id="btn-corrigir" type="button" class="btn btn-warning">Carta de Correção</button>
					<button id="btn-cancelar" type="button" class="btn btn-danger">Cancelar</button>

					@endif

					@if($item->estado == 'cancelado')
					<a target="_blank" href="{{ route('compras.imprimir-danfe', [$item->id]) }}" class="btn btn-info">Imprimir</a>
					<a target="_blank" href="{{ route('compras.imprimir-cancela', [$item->id]) }}" class="btn btn-danger">Imprimir Cancelamento</a>
					@endif

					@if($item->sequencia_cce > 0)
					<a target="_blank" href="{{ route('compras.imprimir-cce', [$item->id]) }}" class="btn btn-warning px-5">Imprimir Correção</a>
					@endif

				</div>
			</div>
			@endif
		</div>
		<input type="hidden" id="compra_id" value="{{ $item->id }}" name="">
		<input type="hidden" id="numero_nfe" value="{{ $item->numero_emissao }}" name="">
	</div>
</div>

<div class="modal fade" id="modal-corrigir" aria-modal="true" role="dialog" style="overflow:scroll;" tabindex="-1">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title">Corrigir NFe <strong class="text-warning numero_nfe"></strong></h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>

			<div class="modal-body">
				<div class="col-md-12">
					{!! Form::text('motivo-corrige', 'Descrição da correção') !!}
				</div>
			</div>

			<div class="modal-footer">
				<button id="btn-corrige-send" type="button" class="btn btn-warning px-5">Corrigir</button>
			</div>

		</div>
	</div>
</div>

<div class="modal fade" id="modal-cancelar" aria-modal="true" role="dialog" style="overflow:scroll;" tabindex="-1">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title">Cancelar NFe <strong class="text-danger numero_nfe"></strong></h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>

			<div class="modal-body">
				<div class="col-md-12">
					{!! Form::text('motivo-cancela', 'Justificativa') !!}
				</div>
			</div>

			<div class="modal-footer">
				<button id="btn-cancelar-send" type="button" class="btn btn-danger px-5">Cancelar</button>
			</div>

		</div>
	</div>
</div>

@endsection

@section('js')
<script type="text/javascript" src="/js/nf_entrada.js"></script>
@endsection
