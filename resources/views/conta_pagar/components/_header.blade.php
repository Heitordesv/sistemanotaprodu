{{-- Bloco de Título e Ação Principal --}}
<div class="d-flex align-items-center mb-3">
    <h5 class="mb-0 text-uppercase">Contas a Pagar</h5>
    <div class="ms-auto">
        <a href="{{ route('conta-pagar.create') }}" class="btn btn-success px-4 shadow-sm">
            <i class="bx bx-plus me-1"></i> Nova Conta
        </a>
        
        {{-- Botão para revelar/esconder filtros --}}
        <button class="btn btn-primary px-4 shadow-sm" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFilters" aria-expanded="false" aria-controls="collapseFilters">
            <i class="bx bx-filter-alt me-1"></i> Filtros
        </button>
    </div>
</div>