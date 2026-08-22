@extends('ecommerce_one_tech.default')
@section('content')

<style type="text/css">
	.checkout__input span{
		font-size: 16px;
	}
	.form-control{
		color: #000;
	}

	table{
		font-size: 15px;
	}

	.checkout__order__subtotal{
		font-size: 16px;
		margin-top: 10px;
	}

	.checkout__order__total{
		font-size: 18px;
	}

	.text-danger{
		font-size: 18px;
	}
</style>
<div class="cart_section">
	<div class="">
		<div class="row">
			<div class="col-lg-10 offset-lg-1">
				<div class="cart_container">
					<div class="cart_title">Realize seu cadastro :)
						<a class="btn btn-info" href="{{$rota}}/login">Já sou cadastrado</a>


						<form method="post">
							@csrf

							<input type="hidden" value="{{$default['config']->empresa_id}}" name="empresa_id">
							<input type="hidden" value="{{$default['carrinho']->id}}" name="pedido_id">
							<br>
							<div class="row">

								<div class="col-lg-8 col-md-12">
									<div class="row">
										<div class="col-lg-6">
											<div class="checkout__input">
												<span>Nome *</span>

												<input  class="form-control" autofocus name="nome" value="{{ old('nome') }}" type="text">
												@if($errors->has('nome'))
												<label class="text-danger">{{ $errors->first('nome') }}</label>
												@endif
											</div>
										</div>
										<div class="col-lg-6">
											<div class="checkout__input">
												<span>Sobre nome *</span>
												<input class="form-control" value="{{ old('sobre_nome') }}" name="sobre_nome" type="text">
												@if($errors->has('sobre_nome'))
												<label class="text-danger">{{ $errors->first('sobre_nome') }}</label>
												@endif
											</div>
										</div>
									</div>

									<div class="row">
										<div class="col-lg-6">
											<div class="checkout__input">

												<span>Telefone *</span>

												<input class="form-control" data-mask="(00) 00000-0000" value="{{old('telefone')}}" name="telefone" type="text">
												@if($errors->has('telefone'))
												<label class="text-danger">{{ $errors->first('telefone') }}</label>
												@endif
											</div>
										</div>
										<div class="col-lg-6">
											<div class="checkout__input">

												<span>Email *</span>
												<input class="form-control" value="{{old('email')}}" name="email" type="text">
												@if($errors->has('email'))
												<label class="text-danger">{{ $errors->first('email') }}</label>
												@endif
											</div>
										</div>
									</div>

									<div class="row">
										<div class="col-lg-4">
											<div class="checkout__input">

												<span>Senha *</span>

												<input class="form-control" name="senha" type="password">
												@if($errors->has('senha'))
												<label class="text-danger">{{ $errors->first('senha') }}</label>
												@endif
											</div>
										</div>

										<div class="col-lg-2">
											<div class="checkout__input">

												<span>Tipo Doc. *</span>

												<select name="tp_doc" id="tp_doc" class="form-control" style="height: 38px;">
													<option @if(old('tp_doc') == 'cpf') selected @endif value="cpf">CPF</option>
													<option @if(old('tp_doc') == 'cnpj') selected @endif value="cnpj">CNPJ</option>
												</select>

											</div>
										</div>
										<div class="col-lg-5">
											<div class="checkout__input">
												<span class="lbl_doc">CPF *</span>

												<input class="form-control" id="doc" value="{{old('cpf')}}" data-mask="000.000.000-00" data-mask-reverse="true" name="cpf" type="tel">
												@if($errors->has('cpf'))
												<label class="text-danger">{{ $errors->first('cpf') }}</label>
												@endif
											</div>
										</div>

										<div class="col-lg-5 ie" style="display: none;">
											<div class="checkout__input">
												<span class="">IE *</span>

												<input class="form-control" id="doc" value="{{old('ie')}}" name="ie" type="tel">
												@if($errors->has('ie'))
												<label class="text-danger">{{ $errors->first('ie') }}</label>
												@endif
											</div>
										</div>


									</div>

									<div class="row">
										<div class="col-lg-9">
											<div class="checkout__input">

												<span>Rua *</span>

												<input class="form-control" value="{{ $enderecoCep != null ? $enderecoCep->logradouro : old('rua') }}" name="rua" type="text">
												@if($errors->has('rua'))
												<label class="text-danger">{{ $errors->first('rua') }}</label>
												@endif
											</div>
										</div>
										<div class="col-lg-3">
											<div class="checkout__input">

												<span>Nº *</span>

												<input class="form-control" value="{{ old('numero') }}" name="numero" type="tel">
												@if($errors->has('numero'))
												<label class="text-danger">{{ $errors->first('numero') }}</label>
												@endif
											</div>
										</div>
									</div>

									<div class="row">
										<div class="col-lg-6">
											<div class="checkout__input">

												<span>Bairro *</span>

												<input class="form-control" value="{{ $enderecoCep != null ? $enderecoCep->bairro : old('bairro') }}" name="bairro" type="text">
												@if($errors->has('bairro'))
												<label class="text-danger">{{ $errors->first('bairro') }}</label>
												@endif
											</div>
										</div>
										<div class="col-lg-6">
											<div class="checkout__input">

												<span>Cidade *</span>

												<input class="form-control" value="{{ $enderecoCep != null ? $enderecoCep->localidade : old('cidade') }}" name="cidade" type="text">
												@if($errors->has('cidade'))
												<label class="text-danger">{{ $errors->first('cidade') }}</label>
												@endif
											</div>
										</div>
									</div>

									<div class="row">
										<div class="col-lg-2">
											<div class="checkout__input">

												<span>UF *</span>
												<select name="uf" id="uf" class="form-control" style="height: 38px;">
													<option value="">--</option>
													@foreach(\App\Models\Cliente::estados() as $uf)
													<option @if($enderecoCep != null) @if($enderecoCep->uf == $uf) selected @endif @endif value="{{$uf}}">{{$uf}}</option>
													@endforeach
												</select>
												@if($errors->has('uf'))
												<label class="text-danger">{{ $errors->first('uf') }}</label>
												@endif
											</div>
										</div>
										<div class="col-lg-4">
											<div class="checkout__input">
												<span>CEP *</span>

												<input class="form-control" data-mask="00000-000" data-mask-reverse="true" value="{{{ $default['carrinho']->observacao != '' ? $default['carrinho']->observacao : old('cep') }}}" name="cep" type="tel">
												@if($errors->has('cep'))
												<label class="text-danger">{{ $errors->first('cep') }}</label>
												@endif
											</div>
										</div>

										<div class="col-lg-4">
											<div class="checkout__input">

												<span>Complemento </span>

												<input class="form-control" value="{{ old('complemento') }}" name="complemento" type="text">
												@if($errors->has('complemento'))
												<label class="text-danger">{{ $errors->first('complemento') }}</label>
												@endif
											</div>
										</div>
									</div>

									<div class="checkout__input">

										<span>Observação </span>

										<input class="form-control" value="{{old('observacao')}}" name="observacao" type="text"
										placeholder="Observação sobre entrega por exemplo">
									</div>


								</div>

								<div class="col-lg-4 col-md-6">
									<div class="cart_container">
										<div class="cart_items" style="margin-top: 40px;">

											<ul class="cart_list">

												<li class="cart_item clearfix">
													<div class="cart_title">Seu Pedido</div>

													<table>
														<thead>
															<tr>
																<th style="width: 77%;">Produto</th>
																<th>Total</th>
															</tr>
														</thead>

														<tbody>
															@if($default['carrinho'] != null)
															@foreach($default['carrinho']->itens as $i)
															<tr>
																<td>{{$i->produto->produto->nome}}
																</td>
																<td>
																	R$ {{number_format($i->produto->valor, 2, ',', '.')}}
																</td>
															</tr>
															@endforeach
															@endif
														</tbody>
													</table>
													<hr>
													<div class="checkout__order__subtotal">Frete <span>R$ {{number_format($default['carrinho']->valor_frete, 2, ',', '.')}}</span></div>
													<div class="checkout__order__total">Total <span>R$ {{ $default['carrinho'] != null ? number_format($default['carrinho']->somaItens() + $default['carrinho']->valor_frete, 2, ',', '.') : '0,00'}}</span></div>
												</li>
											</ul>
										</div>
									</div>
								</div>

							</div>

							<button type="submit" class="button contact_submit_button">SALVAR</button>
						</form>
					</div>

				</div>
			</div>
		</div>
	</div>
</div>
@endsection

@section('javascript')
<script type="text/javascript">
	$(function(){
		changeDoc()
	})
	$('#tp_doc').change((target) => {
		changeDoc()
	})

	function changeDoc(){
		let v = $('#tp_doc').val()

		if(v == 'cpf'){
			$('#doc').mask('000.000.000-00', {reverse: true});
			$('.lbl_doc').html('CPF *');
			$('.ie').css('display', 'none');

		}else{
			$('#doc').mask('00.000.000/0000-00', {reverse: true});
			$('.lbl_doc').html('CNPJ *');
			$('.ie').css('display', 'block');
		}
	}
</script>
@endsection 