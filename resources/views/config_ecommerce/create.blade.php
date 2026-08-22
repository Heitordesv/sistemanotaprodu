@extends('default.layout', ['title' => 'Configurações da Loja Online'])

@section('content')
<div class="page-content">
    <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3 mb-4">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <div class="rounded-circle bg-light-primary text-primary d-flex align-items-center justify-content-center" style="width:46px;height:46px;">
                    <i class="bx bx-store fs-3"></i>
                </div>
                <div>
                    <h4 class="mb-0">Configurações da Loja Online</h4>
                    <small class="text-muted">Organize sua vitrine, entrega, pagamentos e integrações em um só lugar.</small>
                </div>
            </div>
        </div>

        @if(isset($item) && $item)
            <div class="d-flex flex-wrap gap-2">
                <a href="{{ route('configEcommerce.verSite') }}" target="_blank" class="btn btn-light border">
                    <i class="bx bx-show me-1"></i> Ver Loja
                </a>
                <a href="{{ route('ecommerce.mercadopago.security') }}" class="btn btn-outline-primary">
                    <i class="bx bx-credit-card-front me-1"></i> Mercado Pago
                </a>
            </div>
        @endif
    </div>

    @if($errors->any())
        <div class="alert alert-danger border-0 shadow-sm">
            <div class="d-flex align-items-start gap-2">
                <i class="bx bx-error-circle fs-4 mt-1"></i>
                <div>
                    <strong>Revise os campos antes de salvar.</strong>
                    <ul class="mb-0 mt-2 ps-3">
                        @foreach($errors->all() as $erro)
                            <li>{{ $erro }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    @endif

    {!! Form::open()
        ->fill($item)
        ->post()
        ->multipart()
        ->route('configEcommerce.store') !!}

        @include('config_ecommerce._forms')

    {!! Form::close() !!}
</div>
@endsection