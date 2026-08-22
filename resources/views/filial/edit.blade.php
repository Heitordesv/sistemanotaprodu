@extends('default.layout',['title' => 'Editar localização'])
@section('content')
<div class="page-content">
    <div class="card border-top border-0 border-4 border-primary">
        <div class="card-body p-5">
            <div class="page-breadcrumb d-sm-flex align-items-center mb-3">
                <div class="ms-auto">
                    <a href="{{ route('filial.index')}}" type="button" class="btn btn-light btn-sm">
                        <i class="bx bx-arrow-back"></i> Voltar
                    </a>
                </div>
            </div>
            <div class="card-title d-flex align-items-center">
                <h5 class="mb-0 text-primary">Editar localização</h5>
            </div>
            <hr>
            {!!Form::open()->fill($item)
            ->put()
            ->route('filial.update', [$item->id])
            ->multipart()!!}
            <div class="pl-lg-4">
                @include('filial._forms')
            </div>
            {!!Form::close()!!}
        </div>
    </div>
</div>
@endsection
