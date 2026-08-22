<div class="col-md-3 mt-3">
	<label>{{$lbl}}</label>
	<select name="filial_id" required class="form-select filial_id" id="inp-filial_id_create">
		@foreach($locais as $key => $l)
		<option @if($filial_id == $key) selected @endif value="{{$key}}">{{$l}}</option>
		@endforeach
	</select>
</div>