<div class="row mt-3 g-3">

    <div class="col-md-4">
        <div class="card p-3 shadow-sm">
            <small>Total a Receber</small>
            <h5>R$ {{ number_format($totalReceber ?? 0,2,',','.') }}</h5>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card p-3 shadow-sm">
            <small>Total a Pagar</small>
            <h5>R$ {{ number_format($totalPagar ?? 0,2,',','.') }}</h5>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card p-3 shadow-sm">
            <small>Fluxo Previsto</small>
            @php $fluxo = ($totalReceber ?? 0) - ($totalPagar ?? 0); @endphp
            <h5 class="{{ $fluxo >= 0 ? 'text-success' : 'text-danger' }}">
                R$ {{ number_format($fluxo,2,',','.') }}
            </h5>
        </div>
    </div>

</div>