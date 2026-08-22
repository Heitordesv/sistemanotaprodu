<h1>Orçamento</h1>
<h2>{{ $config->razao_social }}</h2>
<h2>Valor: R$ {{ number_format($valor, $config->casas_decimais, ',', '.')}}</h2>
<h2>Emissão: {{ \Carbon\Carbon::parse($emissao)->format('d/m/Y H:i:s')}}</h2>


<h4>Att, {{$usuario}}</h4>