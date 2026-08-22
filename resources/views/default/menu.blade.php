@php
$menu = new App\Helpers\Menu();
$menu = $menu->preparaMenu();
@endphp

@foreach($menu as $m)
@if(!isset($m['ativo']) || $m['ativo'])
<li @if($m['titulo'] == $rotaAtiva) class="mm-active" @endif>
	<a href="javascript:;" class="has-arrow">
		<div class="parent-icon"><i class='{{$m['icone']}}'></i>
		</div>
		<div class="menu-title">{{$m['titulo']}}</div>
	</a>
	<ul>	
		@foreach($m['subs'] as $i)
		@if(!isset($i['rota_ativa']) && $i['rota'] != '')
		<li><a @isset($i['target']) target="_blank" @endisset href="{{$i['rota']}}">
			<i class="bx bx-circle" style="font-size: 10px;"></i>{{$i['nome']}}</a>
		</li>
		@endif
		@endforeach
	</ul>
</li>
@endif
@endforeach

