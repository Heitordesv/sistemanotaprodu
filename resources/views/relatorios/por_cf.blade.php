@extends('relatorios.default')
@section('content')

<table class="table-sm table-borderless"
style="border-bottom: 1px solid rgb(206, 206, 206); margin-bottom:10px;  width: 100%;">
<thead>
    <tr>
        <th width="50%" class="text-left">Produto</th>
        <th width="15%" class="text-left">Valor de venda</th>
        <th width="15%" class="text-left">Valor de compra</th>
        <th width="20%" class="text-left">Categoria</th>
        <th width="20%" class="text-left">CFOP</th>
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

        $cfop = $p->CFOP_saida_estadual ? $p->CFOP_saida_estadual : 'Sem CFOP';
        if (!isset($totaisPorCfop[$cfop])) {
            $totaisPorCfop[$cfop] = 0;
        }
        $totaisPorCfop[$cfop] += $p->valor_venda;
    @endphp
    <tr class="@if($key % 2 == 0) pure-table-odd @endif">
        <td>{{$p->nome}}</td>
        <td>{{number_format($p->valor_venda, 2, ',', '.')}}</td>
        <td>{{number_format($p->valor_compra, 2, ',', '.')}}</td>
        <td>{{$p->categoria->nome}}</td>
        <td>{{ $cfop }}</td>
    </tr>
@endforeach
</tbody>
<tfoot>
    <tr style="font-weight: bold; border-top: 1px solid #ccc;">
        <td class="text-left">Totais</td>
        <td class="text-left">{{number_format($totalVenda, 2, ',', '.')}}</td>
        <td class="text-left">{{number_format($totalCompra, 2, ',', '.')}}</td>
        <td></td>
        <td></td>
    </tr>
    
   
</tfoot>
</table>
 <!-- Exibição dos totais por CFOP -->
    @foreach($totaisPorCfop as $cfop => $total)
       <h6>  Total para CFOP {{ $cfop }}</h6>
       <h6 class="text-left">{{ number_format($total, 2, ',', '.') }}</h6>
    @endforeach

@endsection
