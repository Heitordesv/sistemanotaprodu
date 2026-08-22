@extends('default.layout',['title' => 'Escritório Contábil'])
@section('content')
<div class="page-content">
	<div class="card border-top border-0 border-4 border-primary">
		<div class="card-body p-5">
			<div class="page-breadcrumb d-sm-flex align-items-center mb-3">
			</div>
			<div class="card-title d-flex align-items-center">
				<h5 class="mb-0 text-primary">Escritório Contábil</h5>
			</div>
			<hr>
			
			{!!Form::open()->fill($item)
			->post()
			->autocomplete('off')
			->route('escritorio.store')
			->multipart()!!}
			<div class="pl-lg-4">
				@include('escritorio_contabil._forms')
			</div>
			{!!Form::close()!!}
		</div>
	</div>
</div>
@endsection

@section('js')
<script type="text/javascript">
	
</script>
@endsection