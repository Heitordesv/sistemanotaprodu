@extends('default.layout',['title' => 'Ordem de serviço'])
@section('content')
<div class="page-content">
    <div class="card ">
        <div class="card-body p-4">
            <div class="page-breadcrumb d-sm-flex align-items-center mb-3">
                <div class="ms-auto">
                    <a href="{{ route('ordemServico.create')}}" type="button" class="btn btn-success">
                        <i class="bx bx-plus"></i> Nova ordem de serviço
                    </a>
                </div>
            </div>
            <hr>
            <div class="col mt-2">
                {!!Form::open()->fill(request()->all())
                ->get()
                !!}
                <div class="row">
                    <div class="col-md-5">
                        {!!Form::select('cliente_id', 'Pesquisar por cliente')
                        !!}
                    </div>
                    <div class="col-md-2">
                        {!!Form::date('start_date', 'Data inicial')
                        !!}
                    </div>
                    <div class="col-md-2">
                        {!!Form::date('end_date', 'Data final')
                        !!}
                    </div>
                    <!--<div class="col-md-2">
                        {!!Form::select('estado', 'Estado', [0 => 'Pendente', 1 => 'Aprovado', 2 => 'Reprovado', 3 => 'Finalizado'])->attrs(['class' => 'select2'])
                        !!}
                    </div>-->
                    <div class="col-md-6 text-left">
                        <br>
                        <button class="btn btn-primary" type="submit"> <i class="bx bx-search"></i>Pesquisar</button>
                        <a id="clear-filter" class="btn btn-danger" href="{{ route('ordemServico.index') }}"><i class="bx bx-eraser"></i> Limpar</a>
                    </div>
                </div>
                {!!Form::close()!!}
                <hr />
                @php
    $totalServicosPagos = 0;
    $totalProdutosPagos = 0;
    $totalServicosPendentes = 0;
    $totalProdutosPendentes = 0;
@endphp

@foreach ($data as $item)
    @if ($item->status_pagamento == 1)
        @foreach ($item->servicos as $servico)
            @php $totalServicosPagos += $servico->sub_total; @endphp
        @endforeach
        @foreach ($item->produtos as $produto)
            @php $totalProdutosPagos += $produto->sub_total; @endphp
        @endforeach
    @else
        @foreach ($item->servicos as $servico)
            @php $totalServicosPendentes += $servico->sub_total; @endphp
        @endforeach
        @foreach ($item->produtos as $produto)
            @php $totalProdutosPendentes += $produto->sub_total; @endphp
        @endforeach
    @endif
@endforeach

<div class="container my-5">
    <div class="card shadow-lg border-0 rounded-4">
        <div class="card-body p-4">
            <h4 class="text-center fw-bold mb-4">📊 Resumo Financeiro das Os</h4>
            <div class="row">
                {{-- Pagos --}}
                <div class="col-md-6 mb-4">
                    <div class="p-3 bg-warning bg-opacity-10 rounded-3 border-start border-4 border-success">
                        <h5 class="fw-bold text-success mb-3">✅ Valores Pagos</h5>
                        <p class="mb-1">🛠️ <strong>Serviços:</strong> R$ {{ number_format($totalServicosPagos, 2, ',', '.') }}</p>
                        <p class="mb-1">🛒 <strong>Produtos:</strong> R$ {{ number_format($totalProdutosPagos, 2, ',', '.') }}</p>
                        <hr>
                        <p class="fw-bold text-success">💰 Total Pago: R$ {{ number_format($totalServicosPagos + $totalProdutosPagos, 2, ',', '.') }}</p>
                    </div>
                </div>

                {{-- Pendentes --}}
                <div class="col-md-6 mb-4">
                    <div class="p-3 bg-warning bg-opacity-10 rounded-3 border-start border-4 border-warning">
                        <h5 class="fw-bold text-warning mb-3">❌ Valores a Receber</h5>
                        <p class="mb-1">🛠️ <strong>Serviços:</strong> R$ {{ number_format($totalServicosPendentes, 2, ',', '.') }}</p>
                        <p class="mb-1">🛒 <strong>Produtos:</strong> R$ {{ number_format($totalProdutosPendentes, 2, ',', '.') }}</p>
                        <hr>
                        <p class="fw-bold text-warning">💰 Total Pendente: R$ {{ number_format($totalServicosPendentes + $totalProdutosPendentes, 2, ',', '.') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
