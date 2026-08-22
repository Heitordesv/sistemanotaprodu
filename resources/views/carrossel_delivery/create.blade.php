@extends('default.layout',['title' => 'Novo Carrossel'])
@section('content')
<div class="page-content">
    <div class="card border-top border-0 border-4 border-primary">
        <div class="card-body p-5">
            {!!Form::open()
            ->post()
            ->route('carrosselDelivery.store')
            ->multipart()!!}
            <div class="pl-lg-4">
                @include('carrossel_delivery._forms')
            </div>
            {!!Form::close()!!}
        </div>
    </div>
</div>
@endsection
