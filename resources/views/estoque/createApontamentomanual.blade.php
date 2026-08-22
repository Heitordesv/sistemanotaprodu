@extends('default.layout', ['title' => 'Novo Apontamento'])
@section('content')
    <div class="page-content">
        <div class="card border-top border-0 border-4 border-primary">
            <div class="card-body p-5">

                <h6>Novo Apontamento</h6>

                {!! Form::open()
                ->post()
                ->route('estoque.store')
                !!}
                  <div class="row">
                    <div class="col-md-6 mt-3">
                        {!! Form::select('produto_id', 'Pesquise o Produto') !!}
                    </div>
                    <div class="row">
                        <div class="col-md-3 mt-3">
                            {!! Form::tel('quantidade', 'Quantidade') !!}
                        </div>
                        <div class="col-md-3 mt-3">
                            {!! Form::select('tipo', 'Tipo', [0 => 'Redução de Estoque', 1 => 'Incremento de Estoque']) !!}
                        </div>
                        <br>
                        <div class="col-md-6 mt-3">
                            {!! Form::text('observacao', 'Observação') !!}
                        </div>
                    </div>
                </div>
                <div class="col-12 mt-3">
                    <button type="submit" class="btn btn-primary px-5">Salvar</button>
                </div>

                {!! Form::close() !!}

            </div>
            <div>
            </div>
        </div>

    </div>
@endsection
