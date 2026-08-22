@extends('default.layout', ['title' => 'Emitente'])
@section('content')
<div class="page-content">
    <div class="card border-top border-0 border-4 border-primary">
        <div class="card-body p-5">
            <div class="page-breadcrumb d-sm-flex align-items-center mb-3">
            </div>
            <div class="card-title d-flex align-items-center">
                <h5 class="mb-0 text-primary">Emitente</h5>
            </div>
            <hr>
            {!! Form::open()
            ->fill($item)
            ->post()
            ->route('configNF.store')
            ->multipart() !!}
            <div class="pl-lg-4">
                @include('config_nota._forms')
            </div>
            {!! Form::close() !!}
        </div>
    </div>
</div>
@endsection

