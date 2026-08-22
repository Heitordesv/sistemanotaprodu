@extends('default.layout', ['title' => 'Apontar Item'])
@section('content')
    <div class="page-content">
        <div class="card ">
            <div class="card-body p-4">
                <div class="page-breadcrumb d-sm-flex align-items-center mb-3">
                    <div class="ms-auto">
                        <a href="{{ route('inventario.index') }}" type="button" class="btn btn-light btn-sm">
                            <i class="bx bx-arrow-back"></i> Voltar
                        </a>
                    </div>
                </div>
                <a href="{{ route('inventario.itens', $item->id) }}" type="button" class="btn btn-danger">
                    Lista de itens do inventário
                </a>
                <hr>
                {!! Form::open()->post()->route('inventario.storeApontamento') !!}
				<input type="hidden" value="{{$item->id}}" name="inventario_id">
                <h5>Apontar item</h5>
                <div class="row mt-3">
                    <div class="col-md-4">
                        {!! Form::select('produto_id', 'Produto')->attrs(['class' => 'produto_id'])->required() !!}
                    </div>
                    <div class="col-md-2">
                        {!! Form::tel('quantidade', 'Quantidade')->required() !!}
                    </div>
                    <div class="col-md-2">
                        {!! Form::select('estado', 'Estado', ['' => '--'] + App\Models\ItemInventario::estados())->attrs([
                            'class' => 'form-select',
                        ])->required() !!}
                    </div>
                    <div class="col-md-4">
                        {!! Form::text('observacao', 'Observação') !!}
                    </div>
                    <div class="col-12 mt-3">
                        <button type="submit" class="btn btn-primary px-5">Salvar</button>
                    </div>
				{!!Form::close()!!}
                </div>
            </div>
        </div>
    </div>
@endsection
