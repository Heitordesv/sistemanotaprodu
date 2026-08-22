@extends('ecommerce_one_tech.default')
@section('content')

<div class="blog">
	<div class="container">
		<div class="row">
			<div class="col">
				<div class="blog_posts d-flex flex-row align-items-start justify-content-between">
					<!-- Blog post -->
					@foreach($posts as $p)
					<div class="blog_post">
						<div class="blog_image" style="background-image:url(/{{$p->image}})"></div>
						<div class="blog_text">{{$p->titulo}}</div>
						<div class="blog_text">{{ \Carbon\Carbon::parse($p->created_at)->format('d/m/Y')}}</div>
						<div class="blog_button">
							<a href="{{$rota}}/{{$p->id}}/verPost">Ver mais</a>
						</div>
					</div>
					@endforeach

				</div>
			</div>
		</div>
	</div>
</div>
@endsection