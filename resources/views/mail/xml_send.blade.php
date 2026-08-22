
<h1>{{ $config->razao_social }}</h1>


<h2>Pedido de venda: #{{$venda->id}}</h2>

@if($nf > 0)
<h2>NFe: {{$nf}}</h2>
<h2>Emissão: {{ \Carbon\Carbon::parse($emissao)->format('d/m/Y H:i:s')}}</h2>

@endif

<h2>Valor: R$ {{ number_format($valor, $config->casas_decimais, ',', '.')}}</h2>


<h4>Att, {{$usuario}}</h4>