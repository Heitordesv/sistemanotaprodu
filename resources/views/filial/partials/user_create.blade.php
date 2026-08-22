<div class="col-md-5 col-12">

	<label>{{$lbl}}</label>
	<select id="locais" name="local[]" required class="multiple-select" multiple>
		@foreach($locais as $key => $l)
		<option value="{{$key}}">{{$l}}</option>
		@endforeach
	</select>
</div>