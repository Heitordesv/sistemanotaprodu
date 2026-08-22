@extends('default.layout', ['title' => 'Clonar venda'])
@section('content')
<div class="page-content">
    <div class="card ">
        <div class="card-body p-4">

            <div class="col">
                <h5>Clonar Venda:</h5>
                <div class="row mt-3">
                    <div class="col-6">
                        @if($config->ambiente == 2)
                        <h6>Ambiente: <strong class="text-primary">Homologação</strong></h6>
                        @else
                        <h6>Ambiente: <strong class="text-success">Produção</strong></h6>
                        @endif
                    </div>
                </div>
            </div>
            {!! Form::open()
            ->put()
            ->route('vendas.clone-put', [$item->id]) !!}

            <div class="row mt-4">

                @foreach($semEstoque as $p)
                <p class="text-danger">Produto {{ $p->nome }} sem estoque suficiente</p>
                @endforeach
                
                <div class="col-md-8">
                    <select required class="form-control select2 cliente_id" name="cliente_id" id="inp-cliente_id">
                    </select>
                </div>

                <div class="col-12 mt-2">
                    <button @if(sizeof($semEstoque) > 0) disabled @endif type="submit" class="btn btn-primary px-5">Salvar</button>
                </div>
            </div>

            
            {!! Form::close() !!}

        </div>
    </div>
</div>
@endsection
