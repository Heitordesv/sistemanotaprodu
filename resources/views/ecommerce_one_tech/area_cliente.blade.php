@extends('ecommerce_one_tech.default')
@section('content')

<style type="text/css">
	.form-control{
		color: #000;
	}

	.end{
		margin-top: 10px;
	}
</style>

<div class="container">

	<div class="">
		<h4>Olá, <strong>{{$cliente->nome}}</strong> 
			<button style="color: #fff;" data-toggle="modal" data-target="#exampleModal" class="btn btn-warning">Alterar senha</button>
			<a href="{{$rota}}/logoff" class="btn btn-danger">Logoff</a>
		</h4>
		<hr>

		<div class="row">

			<form method="post" action="/ecommerceUpdateCliente">
				@csrf
				<div class="col-12" style="margin-bottom: 10px;">
					<h4>Dados pessoais</h4>
					<br>
					<div class="row">
						<input type="hidden" value="{{ $cliente->id }}" name="id">
						<div class="col-lg-3 col-6">
							<div class="checkout__input">
								<label>Nome</label>
								<input class="form-control" name="nome" value="{{ $cliente->nome }}" type="text">
								@if($errors->has('nome'))
								<label class="text-danger">{{ $errors->first('nome') }}</label>
								@endif
							</div>
						</div>
						<div class="col-lg-3 col-6">
							<div class="checkout__input">
								<label>Sobre nome</label>
								<input class="form-control" name="sobre_nome" value="{{ $cliente->sobre_nome }}" type="text">
								@if($errors->has('sobre_nome'))
								<label class="text-danger">{{ $errors->first('sobre_nome') }}</label>
								@endif
							</div>
						</div>

						<div class="col-lg-3 col-6">
							<div class="checkout__input">
								<label>Telefone</label>
								<input class="form-control" data-mask="(00) 00000-0000" name="telefone" value="{{ $cliente->telefone }}" type="text">
								@if($errors->has('telefone'))
								<label class="text-danger">{{ $errors->first('telefone') }}</label>
								@endif
							</div>
						</div>

						<div class="col-lg-3 col-6">
							<div class="checkout__input">
								<label>Email</label>
								<input class="form-control" name="email" value="{{ $cliente->email }}" type="text">
								@if($errors->has('email'))
								<label class="text-danger">{{ $errors->first('email') }}</label>
								@endif
							</div>
						</div>
					</div>
					<div class="row">
						<div class="col-12">
							<button type="submit" class="button contact_submit_button">Salvar</button>
						</div>
					</div>
				</div>


			</form>
		</div>
		<hr>

		<!-- pedidos -->
		<div class="row">
			<div class="col-12" style="margin-bottom: 10px;">
				<h4>Seus pedidos</h4>
			</div>

			@foreach($cliente->pedidos() as $p)
			<div class="col-lg-4 col-md-6 char_col">
				<div class="char_item d-flex flex-row align-items-center justify-content-start" style="height: 150px;">
					
					<div class="char_content">
						<ul>

							<h4>Data: <strong>{{ \Carbon\Carbon::parse($p->created_at)->format('d/m/Y H:i')}}</strong></h4>
							<h4>Total: <strong>R$ {{number_format($p->valor_total, 2, ',', '.')}}</strong></h4>
						</ul>

						@if($p->status_preparacao == 0)
						<span class="text-info">Novo</span>
						@elseif($p->status_preparacao == 1)
						<span class="text-primary">Aprovado</span>
						@elseif($p->status_preparacao == 2)
						<span class="text-danger">Cancelado</span>
						@elseif($p->status_preparacao == 3)
						<span class="text-warning">Aguardando Envio</span>
						@elseif($p->status_preparacao == 4)
						<span class="text-dark">Enviado</span>
						@else
						<span class="text-success">Entregue</span>
						@endif

						<a href="{{$rota}}/pedido_detalhe/{{$p->id}}" class="btn btn-success btn-block">Detalhes</a>
					</div>
				</div>
			</div>
			@endforeach

		</div>

		<hr>
		<!-- Endereços -->

		<div class="row">
			<div class="col-12" style="margin-bottom: 10px;">
				<h4>Seus endereços cadastrados</h4>
			</div>

			<div class="col-12" style="margin-bottom: 10px;">
				<button data-toggle="modal" data-target="#modal-endereco" class="btn btn-success">
					<i class="fa fa-plus"></i> Novo Endereço
				</button>
			</div>

			@foreach($cliente->enderecos as $e)
			<div class="col-lg-4 col-md-6 end">
				<div class="char_item d-flex flex-row align-items-center justify-content-start" style="height: 180px;">
					
					<div class="char_content">

						<p>{{$e->rua}}, {{$e->numero}} - {{$e->bairro}}</p>
						<p>{{$e->cidade}} - {{$e->uf}}</p>
						<p>{{$e->cep}}</p>

						<button onclick="edit({{$e}})" class="btn btn-info btn-block">
							<i class="fa fa-edit"></i>
							Editar
						</button>
					</div>
				</div>
			</div>
			@endforeach
		</div>
	</div>
</div>

<div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<form method="post" action="/ecommerceUpdateSenha">
				@csrf
				<div class="modal-header">
					<h5 class="modal-title" id="exampleModalLabel">Alterar Senha</h5>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<div class="modal-body">
					<div class="row">
						<input type="hidden" value="{{ $cliente->id }}" name="id">
						<div class="col-lg-6 col-12">
							<div class="checkout__input">
								<label>Senha</label>
								<input class="form-control" name="senha" value="" type="password">
							</div>
						</div>

						<div class="col-lg-6 col-12">
							<div class="checkout__input">
								<label>Repita Senha</label>
								<input class="form-control" name="repita_senha" value="" type="password">
							</div>
						</div>
					</div>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-danger" data-dismiss="modal">Fechar</button>
					<button type="submit" class="btn btn-success">Salvar</button>
				</div>
			</form>
		</div>
	</div>
</div>

<div class="modal fade" id="modal-endereco" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<form method="post" action="/ecommerceSaveEndereco">
				@csrf
				<div class="modal-header">
					<h5 class="modal-title" id="titulo">Cadastrar Endereço</h5>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<div class="modal-body">
					<div class="row">
						<input type="hidden" value="{{ $cliente->id }}" id="id" name="id">
						<input type="hidden" value="0" id="endereco_id" name="endereco_id">
						<div class="col-lg-8 col-12">
							<div class="checkout__input">
								<label>Rua</label>
								<input class="form-control" required id="rua" name="rua" value="" type="text">
							</div>
						</div>
						<div class="col-lg-4 col-12">
							<div class="checkout__input">
								<label>Nº</label>
								<input class="form-control" required id="numero" name="numero" value="" type="text">
							</div>
						</div>

						<div class="col-lg-6 col-12">
							<div class="checkout__input">
								<label>Bairro</label>
								<input class="form-control" required id="bairro" name="bairro" value="" type="text">
							</div>
						</div>
						<div class="col-lg-6 col-12">
							<div class="checkout__input">
								<label>CEP</label>
								<input class="form-control" id="cep" data-mask="00000-000" data-mask-reverse="true" required name="cep" value="" type="text">
							</div>
						</div>

						<div class="col-lg-8 col-12">
							<div class="checkout__input">
								<label>Cidade</label>
								<input class="form-control" id="cidade" required name="cidade" value="" type="text">
							</div>
						</div>
						<div class="col-lg-4 col-12">
							<div class="checkout__input">
								<label>UF</label>
								<br>
								<select id="uf" required class="custom-select" name="uf" style="margin-left: 0px;">
									<option></option>
									@foreach(App\Models\EnderecoEcommerce::estados() as $u)
									<option value="{{$u}}">{{$u}}</option>
									@endforeach
								</select>
							</div>
						</div>

						<div class="col-12">
							<div class="checkout__input">
								<label>Complemento</label>
								<input class="form-control" id="complemento" name="complemento" value="" type="text">
							</div>
						</div>


					</div>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-danger" data-dismiss="modal">Fechar</button>
					<button type="submit" class="btn btn-success">Salvar</button>
				</div>
			</form>
		</div>
	</div>
</div>


@section('javascript')
<script type="text/javascript">
	function edit(endereco){
		$('#endereco_id').val(endereco.id)

		$('#modal-endereco').modal('show')
		$('#rua').val(endereco.rua)
		$('#numero').val(endereco.numero)
		$('#bairro').val(endereco.bairro)
		$('#cep').val(endereco.cep)
		$('#cidade').val(endereco.cidade)
		$('#cidade').val(endereco.cidade)
		$('#uf').val(endereco.uf).change()
		$('#complemento').val(endereco.complemento)
		$('#titulo').html('Editar Endereço')
	}
</script>
@endsection

@endsection	