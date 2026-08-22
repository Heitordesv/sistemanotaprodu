<div class="row g-3">
    <div class="col-md-4">
        {!! Form::text('nome_cat', 'Nome')->required() !!}
    </div>

 <div class="col-md-4">
        {!! Form::text('desc_cat', 'Descrição')->required() !!}
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
     <!-- Dias da Semana -->
                <div class="col-12">
                    <label for="dia_semana"><span>*</span>Dias em que o item aparece para o cliente:</label>
                    <div class="radio-group">
                        <label>
                            <input type="checkbox" name="dia_semana[]" value="Domingo"> Domingo
                        </label>
                        <label>
                            <input type="checkbox" name="dia_semana[]" value="Segunda"> Segunda
                        </label>
                        <label>
                            <input type="checkbox" name="dia_semana[]" value="Terca"> Terça
                        </label>
                        <label>
                            <input type="checkbox" name="dia_semana[]" value="Quarta"> Quarta
                        </label>
                        <label>
                            <input type="checkbox" name="dia_semana[]" value="Quinta"> Quinta
                        </label>
                        <label>
                            <input type="checkbox" name="dia_semana[]" value="Sexta"> Sexta
                        </label>
                        <label>
                            <input type="checkbox" name="dia_semana[]" value="Sabado"> Sábado
                        </label>
                        <label>
                            <input type="checkbox" id="todosDias" value="Todos"> Todos
                        </label>
                    </div>
                </div>

    
       <div class="col-md-12">
        {!! Form::text('ord', 'ordem')->required() !!}
    </div>
</div>


<script>
    // Função para marcar todos os dias ou desmarcar todos os dias
    document.getElementById('todosDias').addEventListener('change', function() {
        var checkboxes = document.querySelectorAll('input[name="dia_semana[]"]');
        checkboxes.forEach(function(checkbox) {
            checkbox.checked = document.getElementById('todosDias').checked;
        });
    });
</script>
