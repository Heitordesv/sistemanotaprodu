@extends('default.layout', ['title' => 'Configurações Delivery'])
@section('content')
<div class="page-content">
    <div class="card ">
        <div class="card-body p-4">
            <div class="page-breadcrumb d-sm-flex align-items-center mb-3">

            </div>
            <div class="col">
                <h6 class="mb-0 text-uppercase">Cadastrar Configurações de Delivery</h6>
                {!! Form::open()->fill($item)
                ->post()
                ->multipart()
                ->route('configDelivery.store')!!}
                <hr>
                @include('config_delivery._forms')
                {!! Form::close() !!}
            </div>
        </div>
    </div>
</div>
@include('modals.mapa')

@endsection
