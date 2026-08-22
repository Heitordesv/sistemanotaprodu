@extends('default.layout', ['title' => 'Novo Lead'])

@section('content')
@include('leads.components.css')

<div class="page-content leads-page py-4">
    <div class="container-fluid">
        <div class="card border-0 shadow-sm crm-shell mx-auto" style="max-width: 980px;">
            <div class="card-header bg-white border-0 p-4">
                <div class="d-flex justify-content-between align-items-center gap-3 flex-wrap">
                    <div>
                        <div class="text-uppercase text-primary fw-bold small mb-1">CRM Comercial</div>
                        <h4 class="mb-1 fw-bold">Cadastrar novo lead</h4>
                        <p class="text-muted mb-0">Preencha os dados principais para iniciar o atendimento.</p>
                    </div>
                    <a href="{{ route('leads.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-1"></i> Voltar
                    </a>
                </div>
            </div>

            <form action="{{ route('leads.store') }}" method="POST" id="formNovoLead" novalidate autocomplete="off">
                @csrf
                <div class="card-body p-4">
                    @if($errors->any())
                        <div class="alert alert-danger" role="alert">
                            <div class="fw-bold"><i class="fas fa-exclamation-circle me-2"></i>N&atilde;o foi poss&iacute;vel salvar o lead.</div>
                            <div class="small mt-1">Revise os campos destacados e tente novamente.</div>
                        </div>
                    @endif

                    @include('leads.components.form')
                </div>

                <div class="card-footer bg-light border-0 p-4 d-flex justify-content-end gap-2">
                    <a href="{{ route('leads.index') }}" class="btn btn-outline-secondary">Cancelar</a>
                    <button type="submit" class="btn btn-success px-4" id="btnSalvarLead">
                        <i class="fas fa-save me-1"></i> Salvar Lead
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('js')
    @include('leads.components.scripts')
@endsection