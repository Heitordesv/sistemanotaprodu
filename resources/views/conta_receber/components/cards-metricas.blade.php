<div class="row g-3">

    <div class="col-md-3">
        <div class="card border-0 shadow-sm p-3">
            <small>Entradas</small>
            <h4 class="text-success">
                R$ {{ number_format($recebidoMes ?? 0,2,',','.') }}
            </h4>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card border-0 shadow-sm p-3">
            <small>Saídas</small>
            <h4 class="text-danger">
                R$ {{ number_format($pagoMes ?? 0,2,',','.') }}
            </h4>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card border-0 shadow-sm p-3">
            <small>Saldo</small>
            @php $saldo = ($recebidoMes ?? 0) - ($pagoMes ?? 0); @endphp
            <h4 class="{{ $saldo >= 0 ? 'text-success' : 'text-danger' }}">
                R$ {{ number_format($saldo,2,',','.') }}
            </h4>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card border-0 shadow-sm p-3">
            <small>Inadimplência</small>
            <h4 class="text-warning">{{ $inadimplencia ?? 0 }}%</h4>
        </div>
    </div>

</div>