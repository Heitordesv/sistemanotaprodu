<div class="row">
    <div class="col-md-6">
        {!! Form::select('cliente_id', 'Cliente')->attrs(['class' => 'select2',]) 
        ->options(isset($item) ? [$item->cliente_id => $item->cliente->razao_social] : [])
        !!}
    </div>
    <div class="col-md-3">
        {!! Form::date('inicio', 'Data início')->attrs(['class' => ''])
        ->value(date('Y-m-d')) !!}
    </div>
    <div class="col-md-3">
        {!! Form::date('fim', 'Data fim')->attrs(['class' => ''])
        ->value(date('Y-m-d')) !!}
    </div>
    <div class="col-md-12 mt-3">
        {!! Form::text('observacao', 'Observação')->attrs(['class' => '']) !!}
    </div>
    <div class="col-12 mt-5">
        <button type="submit" class="btn btn-primary px-5">Salvar</button>
    </div>
</div>
