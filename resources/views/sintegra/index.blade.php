@extends('default.layout',['title' => 'Sintegra'])
@section('content')
<div class="page-content">
    <div class="card ">
        <div class="card-body p-4">

            <div class="col">
                <h6 class="mb-0 text-uppercase">Gerar Arquivo Sintegra</h6>
                {!!Form::open()->fill(request()->all())
                ->post()
                !!}
                <div class="row">
                    <div class="col-lg-2 col-6">
                        {!! Form::date('start_date', 'Data Inicial') !!}
                    </div>
                    <div class="col-lg-2 col-6">
                        {!! Form::date('end_date', 'Data Final') !!}
                    </div>
                    <div class="col-md-3 text-left">
                        <br>
                        <button class="btn btn-success" type="submit"> <i class="bx bx-file"></i>Gerar</button>
                        
                    </div>
                </div>
                {!!Form::close()!!}
                <hr/>
            </div>

        </div>
    </div>
</div>
@endsection
