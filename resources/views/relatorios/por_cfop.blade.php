@extends('relatorios.default')

@section('content')
<table class="table table-striped table-bordered" style="margin-bottom: 20px; width: 100%;">
    <thead class="thead-light">
        <tr>
            <th width="20%" class="text-left">Data da Venda</th>
            <th width="30%" class="text-left">Produto</th>
            <th width="15%" class="text-left">Valor de Venda</th>
           <!-- <th width="15%" class="text-left">Valor de Compra</th>-->
            <th width="10%" class="text-left">Categoria</th>
            <th width="10%" class="text-left">CFOP</th>
        </tr>
    </thead>
    <tbody>
        @php
            $totalVenda = 0;
            $totalCompra = 0;
            $totaisPorCfop = [];
        @endphp

        @foreach($produtos as $key => $p)
            @php
                $totalVenda += $p->valor_venda;
                $totalCompra += $p->valor_compra;

                $cfop = $p->CFOP_saida_estadual ?: 'Sem CFOP';
                $totaisPorCfop[$cfop] = ($totaisPorCfop[$cfop] ?? 0) + $p->valor_venda;
            @endphp
            <tr>
                <td>{{ \Carbon\Carbon::parse($p->created_at)->format('d/m/Y') }}</td>
                <td>{{ $p->nome }}</td>
                <td>{{ number_format($p->valor_venda, 2, ',', '.') }}</td>
                <!--<td>{{ number_format($p->valor_compra, 2, ',', '.') }}</td>-->
                <td>{{ $p->categoria->nome }}</td>
                <td>{{ $cfop }}</td>
            </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr class="font-weight-bold" style="border-top: 2px solid #ccc;">
            <td colspan="2" class="text-left">Totais</td>
            <td class="text-left">{{ number_format($totalVenda, 2, ',', '.') }}</td>
           <!-- <td class="text-left">{{ number_format($totalCompra, 2, ',', '.') }}</td>-->
            <td></td>
            <td></td>
        </tr>
    </tfoot>
</table>


<table style="width: 100%;">
	<tbody>
		<tr>
		        @foreach($totaisPorCfop as $cfop => $total)

			<th>Totais de vendas para o CFOP {{ $cfop }}:  <strong>{{ number_format($total, 2, ',', '.') }}</strong></th>
	    @endforeach
	</tr>
		
	</tbody>
</table>

@endsection
