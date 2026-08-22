@extends('default.layout', ['title' => 'Dashboard de Gráficos'])

@section('content')
<div class="page-content dashboard-page">
    @include('default.componentsgraficos._styles')

    @if((int) data_get(session('user_logged'), 'adm', 0) === 1)
        @include('default.componentsgraficos._header_filtros')
        @include('default.componentsgraficos._cards_kpi')
        @include('default.componentsgraficos._charts_area')
    @else
        @include('default.componentsgraficos._alerta_erro')
    @endif
</div>
@endsection

@section('js')
    <script src="/assets/js/apexcharts.min.js"></script>
    @php
        $graficoJs = public_path('js/grafico.js');
        $graficoVersao = file_exists($graficoJs) ? filemtime($graficoJs) : time();
    @endphp
    <script src="{{ asset('js/grafico.js') }}?v={{ $graficoVersao }}"></script>
@endsection