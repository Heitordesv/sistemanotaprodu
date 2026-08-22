<div class="row g-3">
    <div class="col-md-6">
        {!!Form::text('url_video', 'URL Vídeo')->required()
        ->attrs(['class' => ''])
        !!}
    </div>
    <div class="col-md-6">
        {!!Form::text('url_sistema', 'Sistema')->required()
        ->attrs(['class' => ''])
        !!}
    </div>
    <div class="col-12">
        <button type="submit" class="btn btn-primary px-5">Salvar</button>
    </div>
</div>
