@extends('default.layout',['title' => 'Dre'])
@section('content')
<div class="page-content">
    <div class="card ">
        <div class="card-body p-4">
            <div class="page-breadcrumb d-sm-flex align-items-center mb-3">
                <div class="ms-auto">
                    <a href="{{ route('dre.list')}}" type="button" class="btn btn-success">
                        <i class="bx bx-list-ol"></i> Lista DRE
                    </a>
                </div>
            </div>
            <hr>
            <div class="col">
                {!!Form::open()
                ->post()
                ->route('dre.store')
                !!}
                <div class="row mt-5" style="margin-left: 190px">
                    <div class="col-md-3">
                        {!!Form::date('inicio', 'Data inicial')
                        !!}
                    </div>
                    <div class="col-md-3">
                        {!!Form::date('fim', 'Data final')
                        !!}
                    </div>
                    <div class="col-md-3">
                        {!!Form::tel('percentual_imposto', '% Imposto')
                        ->attrs(['class' => ''])
                        !!}
                    </div>
                    <div class="col-md-9 mt-3">
                        {!!Form::text('observacao', 'Observação')
                        ->attrs(['class' => ''])
                        !!}
                    </div>
                    <div class="col-12 text-left ">
                        <br>
                        <button class="btn btn-primary" type="submit"></i>Salvar DRE</button>
                    </div>
                </div>
                {!!Form::close()!!}
            </div>
        </div>
    </div>
</div>
@endsection
