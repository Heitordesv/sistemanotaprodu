<div class="row g-3">
    <div class="col-md-6">
        {!!Form::text('nome', 'Nome')->required()
        ->attrs(['class' => 'form-control'])
        !!}
    </div>

    <div class="col-12">
    <button type="submit" class="btn btn-primary px-5">Salvar</button>
        
    </div>
</div>
