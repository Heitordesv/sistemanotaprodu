@extends('default.layout', ['title' => 'Contas a Pagar'])

@section('content')
<div class="page-content">
    {{-- Importante: Verifique se o nome da pasta é conta_pagar ou contasPagar --}}
    @include('conta_pagar.components._header')
    
    <div class="collapse {{ request()->has('fornecedor_id') ? 'show' : '' }}" id="collapseFilters">
        @include('conta_pagar.components._filters')
    </div>

    @php
        use Carbon\Carbon;
        $dataAtual = Carbon::now()->format('Y-m-d');
        $dataPertoVencimento = Carbon::now()->addDays(5)->format('Y-m-d');

        // Cálculos baseados no $dashboardData
        $totalValorPendente = $dashboardData->where('status', 0)->sum('valor_integral');
        $totalPendentes = $dashboardData->where('status', 0)->count();

        $totalValorPago = $dashboardData->where('status', 1)->sum('valor_pago');
        $totalPagas = $dashboardData->where('status', 1)->count();

        $vencidas = $dashboardData->filter(fn($c) => $c->status == 0 && $c->data_vencimento < $dataAtual);
        $totalVencidasPendentes = $vencidas->count();
        $totalValorVencidoPendente = $vencidas->sum('valor_integral');

        $totalGeral = $totalValorPendente + $totalValorPago;
    @endphp
    
    @include('conta_pagar.components._stats')

    <div class="card shadow-sm border-0 radius-10">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-borderless align-middle mb-0">
    <thead style="background: #f8fafc; border-bottom: 2px solid #e2e8f0;">
        <tr>
                        <th class="ps-4 py-3 text-uppercase text-secondary" style="font-size: 0.75rem; letter-spacing: 1px;"></th>

            <th class="ps-4 py-3 text-uppercase text-secondary" style="font-size: 0.75rem; letter-spacing: 1px;">Fornecedor</th>
            <th class="py-3 text-uppercase text-secondary" style="font-size: 0.75rem; letter-spacing: 1px;">Status</th>
            <th class="py-3 text-end pe-4 text-uppercase text-secondary" style="font-size: 0.75rem; letter-spacing: 1px;">Financeiro</th>
            @if(empresaComFilial()) <th class="py-3 text-uppercase text-secondary" style="font-size: 0.75rem; letter-spacing: 1px;">Local</th> @endif
            <th class="py-3 text-center text-uppercase text-secondary" style="font-size: 0.75rem; letter-spacing: 1px;">Ações</th>
        </tr>
    </thead>
 
                    <tbody>
                        @forelse($data as $item)
                            {{-- AQUI ESTAVA O ERRO: FALTAVA O INCLUDE DA LINHA --}}
                            @include('conta_pagar.components._row', [
                                'item' => $item, 
                                'dataAtual' => $dataAtual, 
                                'dataPertoVencimento' => $dataPertoVencimento
                            ])
                        @empty
                            <tr>
                                <td colspan="10" class="text-center py-5 text-secondary">
                                    <i class="bx bx-folder-open fs-1"></i><br>
                                    Nenhum registro encontrado.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <div class="mt-3">
                {{ $data->appends(request()->query())->links() }}
            </div>
        </div>
    </div>
</div>

{{-- Modais e Scripts --}}
@include('conta_pagar.components._modals')
@include('conta_pagar.components._scripts')

@endsection