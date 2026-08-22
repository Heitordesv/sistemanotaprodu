<div class="col-md-3">
	<label>{{$lbl}}</label>
	<select name="local[]" required class="multiple-select" data-placeholder="Choose anything" multiple="multiple">
		@foreach($locais as $key => $l)
		<option value="{{$key}}">{{$l}}</option>
		@endforeach
	</select>
</div>