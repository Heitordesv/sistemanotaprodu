@extends('default.layout', ['title' => 'Editar Lista'])
@section('content')
    <div class="page-content">
        <div class="card border-top border-0 border-4 border-primary">
            <div class="card-body p-5">
                <div class="page-breadcrumb d-sm-flex align-items-center mb-3">
                    <div class="ms-auto">
                        <a href="{{ route('listaDePrecos.index') }}" type="button" class="btn btn-light btn-sm">
                            <i class="bx bx-arrow-back"></i> Voltar
                        </a>
                    </div>
                </div>
                <hr>
                <h4>Atualizar Preço</h4>

                <h5>Produto: <strong class="text-danger">{{ $item->produto->nome }}</strong></h5>
                <h5>Valor Atualizado: {{ __moeda($item->valor) }}</h5>


                {!! Form::open()->post()->route('listaDePrecos.storeValor') !!}

                <input type="hidden" name="id" value="{{ $item->id }}">

                <div class="row mt-5">
                    <div class="col-md-5">
                        {!! Form::tel('novo_valor', 'Novo Valor')->attrs(['class' => 'moeda']) !!}
                    </div>
                    <div class="col-md-12 mt-3">
                        <button class="btn btn-info" type="submit">Salvar</button>
                    </div>
                {!! Form::close() !!}

                   
                </div>
                
            </div>
        </div>
    </div>
@endsection
