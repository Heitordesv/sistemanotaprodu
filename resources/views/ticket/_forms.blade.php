<div class="row g-3">
    <div class="col-md-5">
        {!!Form::select('departamento', 'Como podemos ajudar?', App\Models\Ticket::departamentos())
            ->attrs(['class' => 'form-select'])!!}
    </div>

    <div class="col-md-7">
        {!!Form::text('assunto', 'Assunto')
            ->attrs(['placeholder' => 'Ex.: Erro ao emitir NF-e'])
            ->required()!!}
    </div>

    <div class="col-12">
        {!!Form::textarea('mensagem', 'Descreva o que está acontecendo')
            ->attrs([
                'placeholder' => 'Conte o que você estava fazendo, o que aconteceu e, se houver, informe a mensagem de erro.',
                'rows' => 6,
                'maxlength' => 5000
            ])
            ->required()!!}
        <small class="text-muted">Quanto mais detalhes você informar, mais rápido conseguimos ajudar.</small>
    </div>

    <div class="col-12">
        <label for="image-upload" class="form-label">Imagem do erro <span class="text-muted">(opcional, até 2 MB)</span></label>
        <input type="file" name="image" id="image-upload" class="form-control" accept="image/*">
        <div id="support-image-preview" class="mt-2 d-none">
            <img id="support-image-preview-img" src="" alt="Prévia do anexo" style="max-width:320px;max-height:220px;object-fit:contain;border-radius:10px;border:1px solid #e5e7eb;">
        </div>
    </div>

    <div class="col-12 d-flex justify-content-end gap-2 mt-4">
        <a href="{{ route('tickets.index') }}" class="btn btn-light">Cancelar</a>
        <button class="btn btn-primary px-4" type="submit">
            <i class='bx bx-message-square-dots'></i> Iniciar atendimento
        </button>
    </div>
</div>

@section('js')
<script>
(function(){
    const input = document.getElementById('image-upload');
    const wrap = document.getElementById('support-image-preview');
    const img = document.getElementById('support-image-preview-img');
    if(!input) return;
    input.addEventListener('change', function(){
        const file = this.files && this.files[0];
        if(!file){ wrap.classList.add('d-none'); return; }
        img.src = URL.createObjectURL(file);
        wrap.classList.remove('d-none');
    });
})();
</script>
@endsection