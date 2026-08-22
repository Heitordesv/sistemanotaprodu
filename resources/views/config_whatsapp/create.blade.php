@extends('default.layout', ['title' => 'Configurações Mercado Pago'])
@section('content')
<div class="page-content">
    <div class="card ">
        <div class="card-body p-4">
            <div class="page-breadcrumb d-sm-flex align-items-center mb-3">
            </div>
            <div class="col">
                <h6 class="mb-0 text-uppercase">Cadastrar Configurações de Ecommerce</h6>
                {!! Form::open()->fill($item)
                ->post()
                ->multipart()
                ->route('configEcommerce.store')!!}
                <hr>
                @include('config_ecommerce._forms')
                {!! Form::close() !!}
            </div>
        </div>
    </div>
</div>
@endsection
