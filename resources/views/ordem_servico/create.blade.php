@extends('default.layout',['title' => 'Nova Ordem Serviço'])
@section('content')
<div class="page-content">
    <div class="card border-top border-0 border-4 border-primary">
        <div class="card-body p-5">
            <div class="page-breadcrumb d-sm-flex align-items-center mb-3">
                <div class="ms-auto">
                    <a href="{{ route('ordemServico.index')}}" type="button" class="btn btn-light btn-sm">
                        <i class="bx bx-arrow-back"></i> Voltar
                    </a>
                </div>
            </div>
            <div class="card-title d-flex align-items-center">
                <h5 class="mb-0 text-primary">Nova ordem serviço</h5>
            </div>
            <hr>
            {!!Form::open()
            ->post()
            ->route('ordemServico.store')
            !!}
            <div class="pl-lg-4">
                @include('ordem_servico._forms')
            </div>
            {!!Form::close()!!}
        </div>
    </div>
</div>

@section('js')
<script type="text/javascript" src="/js/client.js"></script>
<script type="text/javascript" src="/js/ordem_servico.js"></script>
@endsection

@include('modals._client', ['not_submit' => true])

@endsection
