@extends('default.layout', ['title' => 'Configurações Empresa'])
@section('content')
<div class="page-content">
    <div class="card ">
        <div class="card-body p-4">
            <div class="page-breadcrumb d-sm-flex align-items-center mb-3">
                {{-- <div class="ms-auto">
                    <a href="{{ route('config.index')}}" type="button" class="btn btn-light btn-sm">
                        <i class="bx bx-arrow-back"></i> Voltar
                    </a>
                </div> --}}
            </div>
            <div class="col">
                <h6 class="mb-0 text-uppercase">Configuração de empresa</h6>
                {!! Form::open()
                ->post()
                ->multipart()
                ->route('config.store')!!}
                <hr>
                @include('config_empresa._forms')
                {!! Form::close() !!}
            </div>
        </div>
    </div>
</div>

@endsection
