<!DOCTYPE html>
<html>
<head>
	<title></title>
	<link rel="stylesheet" href="https://unpkg.com/purecss@1.0.1/build/pure-min.css" integrity="sha384-oAOxQR6DkCoMliIh8yFnu25d7Eq/PHS21PClpwjOTeU2jRSq11vu66rf90/cZr47" crossorigin="anonymous">
	<style type="text/css">
		.soma{
			font-size: 30px;
		}

		.center{
			text-align: center;
			line-height: 0.5;
		}

		.text-success{
			color: #00e676;
		}

		.text-info{
			color: #8D56FC;
		}

		.bg-info{
			background: #8D56FC;
			color: #fff;
		}

		.bg-success{
			background: #00e676;
			color: #fff;
		}

		.text-danger{
			color: #e53935;
		}

		.bg-danger{
			background: #e53935;
			color: #fff;
		}

		tr{
			height: 70px;
		}
		th{
			font-size: 20px;
			height: 70px;
			margin-left: 10px;

		}
		td{
			font-size: 16px;
			height: 40px;
			margin-left: 5px;
		}

	</style>
</head>
<body>
	<div class="row">
		<div class="col s12">
			<h3 class="center-align">DRE {{ \Carbon\Carbon::parse($dre->inicio)->format('d/m/Y')}} - {{ \Carbon\Carbon::parse($dre->fim)->format('d/m/Y')}}</h3>

			@if($tributacao->regime != 1)

			<h2 class="card-title">% Imposto: 
				<strong class="text-primary">{{number_format($dre->percentual_imposto, 2, ',', '.')}}
				</strong>
			</h2>

			@endif

			<h2 class="card-title">Observação: 
				<strong class="text-info">
					{{ $dre->observacao != "" ? $dre->observacao : "--" }}
				</strong>
			</h2>
		</div>

		
		<table>
			@foreach($dre->categorias as $key => $c)

			<tbody>

				<tr class="bg-success">
					<th width="350">{{$c->nome}}</th>
					<th width="180"></th>
				</tr>

				@foreach($c->lancamentos as $l)

				<tr>
					<td width="350">{{$l->nome}}</td>
					<td width="180">R$ {{ number_format($l->valor, 2, ',', '.') }}</td>
				</tr>


				@endforeach

				@if($key > 2)
				<tr class="text-info">
					<th width="350">{{$c->nome}}</th>
					<th width="180">R$ {{ number_format($c->soma(), 2, ',', '.') }}</th>
				</tr>
				@endif

			</tbody>

			@endforeach

			

			<tr class="@if($dre->lucro_prejuizo >= 0) bg-success @else bg-danger @endif">
				<th width="350">Lucro (Prejuizo) no Período</th>
				<th width="180">R$ {{ number_format($dre->lucro_prejuizo, 2, ',', '.') }}</th>
			</tr>

		</table>


	</div>
</body>
