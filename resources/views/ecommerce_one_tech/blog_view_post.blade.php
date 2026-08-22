@extends('ecommerce_one_tech.default')
@section('content')
<style type="text/css">
	.img-autor{
		width: 40px;
		border: 1px solid #999;
		border-radius: 50%;
	}
</style>

<div class="home" style="height: 400px;">
	<div class="home_background parallax-window" data-parallax="scroll" data-image-src="/ecommerce/posts/{{$post->img}}" data-speed="0.8"></div>
</div>

<div class="single_post" style="margin-top: 10px;">
	<div class="container">
		<div class="row">
			<div class="col-lg-8 offset-lg-2">
				<div style="font-size: 25px;" class="single_post_title">{{$post->titulo}}</div>

				<div class="single_post_text">
					{!! $post->texto !!}
				</div>

				<div class="blog__details__author">
					<div class="blog__details__author__pic">
						<img class="img-autor" src="/ecommerce/autores/{{$post->autor->img}}" alt="">
					</div>
					<div class="blog__details__author__text">
						<h6>Autor: {{$post->autor->nome}}</h6>
						<span>{{$post->autor->tipo}}</span>
					</div>
				</div>

			</div>
		</div>
	</div>
</div>

<div class="blog">
	<div class="container">
		<div class="row">
			<div class="col">
				<div class="blog_posts d-flex flex-row align-items-start justify-content-between">

					@foreach($postsRecentes as $p)
					@if($post->id != $p->id)
					<div class="blog_post">
						<div class="blog_image" style="background-image:url(/ecommerce/posts/{{$p->img}})"></div>
						<div class="blog_text">{{$p->titulo}}</div>
						<div class="blog_text">{{ \Carbon\Carbon::parse($p->created_at)->format('d/m/Y')}}</div>
						<div class="blog_button">
							<a href="{{$rota}}/{{$p->id}}/verPost">Continuar lendo</a>
						</div>
					</div>
					@endif
					@endforeach

				</div>
			</div>	
		</div>
	</div>
</div>

@endsection