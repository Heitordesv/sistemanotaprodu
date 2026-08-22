@extends('default.layout',['title' => 'Alterar estado fiscal'])
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

</style>
@endsection

<div class="page-content">
	<div class="card border-top border-0 border-4 border-primary">
		<div class="card-body p-5">
			<div class="page-breadcrumb d-sm-flex align-items-center mb-3">
				<div class="ms-auto">
					<a href="{{ route('vendas.index')}}" type="button" class="btn btn-light btn-sm">
						<i class="bx bx-arrow-back"></i> Voltar
					</a>
				</div>
			</div>
			<div class="card-title d-flex align-items-center">
				<h5 class="mb-0 text-primary">Alterar estado fiscal da venda</h5>

			</div>

			<div class="card">
				<div class="card-body">
					<h5>Cliente: <strong class="text-primary">{{$item->cliente->razao_social}}</strong></h5>
					<h6>CNPJ: <strong class="text-success">{{$item->cliente->cpf_cnpj}}</strong></h6>
					<h6>Data: <strong class="text-success">{{ \Carbon\Carbon::parse($item->data_registro)->format('d/m/Y H:i:s')}}</strong></h6>
					<h6>Valor Total: <strong class="text-success">{{ number_format($item->valor_total, 2, ',', '.') }}</strong></h6>
					<h6>Cidade: <strong class="text-success">{{ $item->cliente->cidade->nome }} ({{ $item->cliente->cidade->uf }})</strong></h6>
					<h6>Chave NFe: <strong class="text-success">{{$item->chave != "" ? $item->chave : '--'}}</strong></h6>
				</div>
			</div>
			<hr>
			
			{!!Form::open()
			->put()
			->route('nfe.update-state', [$item->id])
			->multipart()!!}
			<div class="pl-lg-4">

				<div class="row g-3">
					<div class="col-md-3">
						{!!Form::select('estado_emissao', 'Estado', 
						['novo' => 'Novo', 'rejeitado' => 'Rejeitado', 'cancelado' => 'Cancelado', 'aprovado' => 'Aprovado']
						)
						->attrs(['class' => 'form-select'])
						->value(isset($item) ? $item->estado_emissao : '')
						!!}
					</div>
					<div class="col-md-3 mt-3 file">
						{!! Form::file('xml', 'XML')
						->attrs(['accept' => '.xml']) !!}
						<span class="text-danger" id="filename"></span>
					</div>

					<div class="col-12">
						<button type="submit" class="btn btn-primary px-5">Salvar</button>
					</div>
				</div>

			</div>
			{!!Form::close()!!}
		</div>
	</div>
</div>
@endsection