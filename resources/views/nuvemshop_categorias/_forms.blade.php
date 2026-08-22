<div class="row g-3">
	<div class="col-md-3">
		{!!Form::text('nome', 'Nome')
		->required()
		->value(isset($item) ? $item->name->pt : '')
		!!}
	</div>

	<div class="col-md-3">
		<label>Categoria</label>
		<select name="categoria_id" class="form-select">
			<option value="">--</option>
			@foreach($categorias as $c)
			<option @isset($categoria) @if($c->id == $categoria->parent) selected @endif @endif value="{{$c->id}}">{{$c->name->pt}}</option>
			@endforeach
		</select>
	</div>

	<div class="col-md-6">
		{!!Form::textarea('descricao', 'Descrição')
		->value(isset($item) ? $item->description->pt : '')
		!!}
	</div>

	<div class="col-12">
		<button type="submit" class="btn btn-primary px-5">Salvar</button>

	</div>
</div>