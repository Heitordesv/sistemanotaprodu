<div class="card shadow-sm border-0 mb-4">
    <div class="card-body d-flex justify-content-between align-items-center flex-wrap gap-2">
        {{-- LADO ESQUERDO --}}
        <div>
            <h5 class="mb-0 fw-bold text-primary">
                <i class="bx bx-dollar-circle me-2"></i> Contas a Receber
            </h5>
            <small class="text-muted">Gerencie seus recebimentos</small>
        </div>

        {{-- LADO DIREITO --}}
        <div class="d-flex gap-2">

            {{-- RATEIO --}}
            <a href="{{ route('conta-receber.rateio-grupo') }}" 
               class="btn btn-outline-primary"
               title="Rateio por Grupo">
                <i class="bx bx-sitemap"></i>
            </a>

            {{-- NOVA CONTA --}}
            <a href="{{ route('conta-receber.create') }}" 
               class="btn btn-success">
                <i class="bx bx-plus me-1"></i> Nova
            </a>

            {{-- FILTRO --}}
            <button class="btn btn-outline-secondary"
                    data-bs-toggle="collapse"
                    data-bs-target="#filterCollapse"
                    title="Filtros">
                <i class="bx bx-filter"></i>
            </button>

        </div>

    </div>
</div>