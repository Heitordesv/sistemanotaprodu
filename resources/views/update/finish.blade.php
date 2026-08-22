@extends('default.layout',['title' => 'SQL'])
@section('content')
<div class=" d-flex flex-column flex-column-fluid" id="kt_content">
	<div class="card card-custom gutter-b example example-compact">
		<div class="container @if(env('ANIMACAO')) animate__animated @endif animate__backInLeft">
			<div class="col-lg-12">
				<br>
				
				@foreach($logMessage as $log)
				<p>{!! $log !!}</p>
				@endforeach
				
			</div>

			<a href="{{ route('appUpdate.sql') }}" class="btn btn-info mb-4 ml-4">
				<i class="la la-arrow-alt-circle-left"></i>
				voltar
			</a>
		</div>
	</div>
</div>

@endsection

@section('javascript')
<script type="text/javascript">
	
</script>
@endsection