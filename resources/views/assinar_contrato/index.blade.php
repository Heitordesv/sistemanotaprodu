@extends('default.layout',['title' => 'Contador'])
@section('content')
<div class="page-content">
    <div class="card ">
        <div class="card-body">
            <h4>Contrato:</h4>
            <div class="card">
                <h6 class="m-5">
                    {!! $texto !!}
                </h6>
            </div>
            <br>
            {!! Form::open()
            ->post()
            ->route('assinarContrato.assinar')
            !!}
            @csrf
            <div class="row">
                <div class="">
                    <div class="switch switch-outline switch-success">
                        <label class="">
                            <input value="true" name="aceito" class="red-text" type="checkbox">
                            <label>Aceito os termos</label>
                            <span class="lever"></span>
                        </label>
                    </div>
                </div>
            </div>
            <div class="card-footer">
                <div class="row">
                    <div class="col-xl-2">
                    </div>
                    <div class="col-lg-3 col-sm-6 col-md-4">
                        <a style="width: 100%" class="btn btn-danger" href="/graficos">
                            <i class="la la-close"></i>
                            <span class="">Cancelar</span>
                        </a>
                    </div>
                    <div class="col-lg-3 col-sm-6 col-md-4">
                        <button style="width: 100%" type="submit" class="btn btn-success">
                            <i class="la la-check"></i>
                            <span class="">Salvar</span>
                        </button>
                    </div>
                </div>
            </div>
            {!! Form::close() !!}
        </div>
    </div>
</div>
@endsection
