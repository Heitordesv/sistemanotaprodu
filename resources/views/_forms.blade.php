<div class="row g-3">
    <div class="col-md-4">
        {!! Form::text('nome_cat', 'Nome')->required() !!}
    </div>

    <div class="form-group">
        <label for="icon_cat">Ícone (Imagem)</label>
        <input type="file" name="icon_cat" id="icon_cat" class="form-control" />
    </div>
    <div class="col-md-4">
        {!! Form::time('hora_abertura', 'Hora de Abertura')->required() !!}
    </div>

    <div class="col-md-4">
        {!! Form::time('hora_fechamento', 'Hora de Fechamento')->required() !!}
    </div>

    <div class="col-12">
        <button type="submit" class="btn btn-primary px-5">Salvar</button>
    </div>
</div>

<!-- Adicione o script abaixo para a pré-visualização -->
<script>
    document.getElementById('icon_cat').addEventListener('change', function(event) {
        var file = event.target.files[0];
        if (file) {
            var reader = new FileReader();
            reader.onload = function(e) {
                var imagePreview = document.getElementById('imagePreview');
                imagePreview.style.display = 'block';
                imagePreview.src = e.target.result; // Define o conteúdo da pré-visualização
            };
            reader.readAsDataURL(file);
        }
    });
</script>