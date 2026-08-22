<div class="col-md-3 col-12">
	<label>Filtro</label>
	<select id="locais" name="filial_id" class="form-select">
        <option value="todos">Todos</option>
		@foreach($locais as $key => $l)
		<option @if($filial_id == $key) selected @endif value="{{$key}}">{{$l}}</option>
		@endforeach
	</select>
</div>