@extends('default.layout', ['title' => 'Fechamento de Caixa'])
@section('content')
<div class="page-content">
    <div class="card">
        <div class="card-body p-4">
            @if ($abertura != null)
                @php $resumoCaixa = $abertura->resumoCaixa(now()); @endphp
                {!! Form::open()->post()->route('frenteCaixa.fecharPost') !!}
                <input type="hidden" name="abertura_id" value="{{ $abertura->id }}">

                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                    <div><h4 class="mb-1">Fechamento do Caixa #{{ $abertura->id }}</h4><div class="text-muted">Operador: <strong>{{ $abertura->usuario->nome ?? 'Não identificado' }}</strong></div></div>
                    <span class="badge bg-success fs-6">CAIXA ABERTO</span>
                </div>
                <div class="alert alert-info"><i class="bx bx-info-circle me-1"></i> Este fechamento afeta somente o <strong>Caixa #{{ $abertura->id }}</strong> do operador <strong>{{ $abertura->usuario->nome ?? 'atual' }}</strong>. Outros caixas permanecem independentes.</div>

                <div class="row g-3 mb-4">
                    <div class="col-md-3"><div class="card border shadow-sm h-100"><div class="card-body"><small class="text-muted">Valor de abertura</small><h5 class="mb-0">R$ {{ __moeda($abertura->valor) }}</h5></div></div></div>
                    <div class="col-md-3"><div class="card border shadow-sm h-100"><div class="card-body"><small class="text-muted">Recebimentos de contas</small><h5 class="mb-0 text-success">R$ {{ __moeda($resumoCaixa['totalRecebimentos']) }}</h5></div></div></div>
                    <div class="col-md-3"><div class="card border shadow-sm h-100"><div class="card-body"><small class="text-muted">Resultado financeiro</small><h5 class="mb-0 text-info">R$ {{ __moeda($resumoCaixa['resultadoFinanceiro']) }}</h5></div></div></div>
                    <div class="col-md-3"><div class="card border shadow-sm h-100"><div class="card-body"><small class="text-muted">Dinheiro na gaveta</small><h5 class="mb-0 text-warning">R$ {{ __moeda($resumoCaixa['dinheiroNaGaveta']) }}</h5></div></div></div>
                </div>

                <h5>Total de vendas deste caixa: {{ sizeof($vendas) }}</h5>
                <h6 class="mt-3">Totais das vendas por tipo de pagamento</h6>
                <div class="row mt-2 mb-3">
                    @foreach ($somaTiposPagamento as $key => $tp)
                        @if ($tp > 0)
                            <div class="col-sm-4 col-lg-3 col-md-6 mb-3"><div class="card border h-100"><div class="card-header"><h6 class="card-title mb-0">{{ App\Models\VendaCaixa::getTipoPagamento($key) }}</h6></div><div class="card-body"><h4 class="text-success mb-0">R$ {{ __moeda($tp) }}</h4></div></div></div>
                        @endif
                    @endforeach
                </div>

                <div class="table-responsive mt-3"><table class="table mb-0 table-striped align-middle">
                    <thead><tr><th>Cliente</th><th>Data</th><th>Pagamento</th><th>Estado</th><th>NFC-e / NF-e</th><th>Tipo</th><th>Valor</th></tr></thead><tbody>
                    @forelse ($vendas as $item)
                        <tr><td>{{ $item->cliente->razao_social ?? 'Consumidor Final' }}</td><td>{{ __data_pt($item->created_at, 0) }}</td><td>{{ $item->tipo_pagamento == '99' ? 'Múltiplo' : $item->getTipoPagamento($item->tipo_pagamento) }}</td><td>{{ $item->estado_emissao }} {{ $item->estado }}</td><td>{{ $item->NFcNumero }} {{ $item->numero_nfe }}</td><td>{{ $item->tipo }}</td><td>R$ {{ __moeda($item->valor_total) }}</td></tr>
                    @empty<tr><td colspan="7" class="text-center py-4">Nenhuma venda neste caixa.</td></tr>@endforelse
                    </tbody>
                </table></div>

                <div class="card border mt-4"><div class="card-header"><h5 class="mb-0">Recebimentos de contas deste caixa</h5></div><div class="table-responsive"><table class="table table-striped mb-0 align-middle">
                    <thead><tr><th>Conta</th><th>Recebido por</th><th>Forma</th><th>Horário</th><th class="text-end">Valor</th></tr></thead><tbody>
                    @forelse($resumoCaixa['recebimentos'] as $r)
                        <tr><td>#{{ $r->conta_receber_id }}</td><td>{{ $r->usuario_nome }}</td><td>{{ $r->tipo_pagamento_nome }}</td><td>{{ $r->received_at ? \Carbon\Carbon::parse($r->received_at)->format('d/m/Y H:i') : '--' }}</td><td class="text-end"><strong>R$ {{ __moeda($r->valor) }}</strong></td></tr>
                    @empty<tr><td colspan="5" class="text-center py-3">Nenhum recebimento de conta neste caixa.</td></tr>@endforelse
                    </tbody><tfoot><tr><th colspan="4">Total de recebimentos</th><th class="text-end">R$ {{ __moeda($resumoCaixa['totalRecebimentos']) }}</th></tr></tfoot>
                </table></div></div>

                @if(sizeof($vendas) == 0 && $resumoCaixa['totalRecebimentos'] > 0)
                    <div class="alert alert-success mt-3"><i class="bx bx-check-circle me-1"></i> Este caixa possui recebimentos de contas e pode ser fechado normalmente mesmo sem vendas.</div>
                @elseif(sizeof($vendas) == 0)
                    <div class="alert alert-light border mt-3"><i class="bx bx-info-circle me-1"></i> Este caixa não possui vendas. O fechamento continua permitido e preservará os demais movimentos registrados.</div>
                @endif
                <div class="alert alert-secondary mt-3">PIX/cartão entram no resultado financeiro. Somente dinheiro entra fisicamente na gaveta.</div>
                <div class="mt-3"><button class="btn btn-warning" type="submit"><i class="bx bx-lock-alt"></i> Fechar somente o Caixa #{{ $abertura->id }}</button></div>
                {!! Form::close() !!}
            @else
                <div class="alert alert-warning mb-0">Não existe caixa aberto para este usuário.</div>
            @endif
        </div>
    </div>
</div>
@endsection
