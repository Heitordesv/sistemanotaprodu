<div class="row g-3">
    <div class="col-md-4">
        {!! Form::text('nome_cat', 'Nome', $item->nome_cat)->required() !!}
    </div>
 <div class="col-md-4">
        {!! Form::text('desc_cat', 'Descrição')->required() !!}
    </div>
    <div class="col-md-4">
        {!! Form::time('hora_abertura', 'Hora de Abertura', $item->hora_abertura)->required() !!}
    </div>

    <div class="col-md-4">
        {!! Form::time('hora_fechamento', 'Hora de Fechamento', $item->hora_fechamento)->required() !!}
    </div>

    <!-- Dias da Semana -->
    <div class="col-12">
        <label for="dia_semana"><span>*</span>Dias em que o item aparece para o cliente:</label>
        <div class="radio-group">
            @php
                $dias = ['Domingo', 'Segunda', 'Terca', 'Quarta', 'Quinta', 'Sexta', 'Sabado'];
                $selecionados = $item->dias_semana_array ?? [];
            @endphp

            @foreach ($dias as $dia)
                <label>
                    <input type="checkbox" name="dia_semana[]" value="{{ $dia }}" 
                        {{ in_array($dia, $selecionados) ? 'checked' : '' }}>
                    {{ $dia }}
                </label>
            @endforeach

            <label>
                <input type="checkbox" id="todosDias" value="Todos" 
                    {{ count($selecionados) === count($dias) ? 'checked' : '' }}>
                Todos
            </label>
        </div>
    </div>

    <div class="col-md-12">
        {!! Form::text('ord', 'Ordem', $item->ord)->required() !!}
    </div>

    <div class="col-12">
        <button type="submit" class="btn btn-primary px-5">Salvar</button>
    </div>
</div>

<script>
    // Marcar/desmarcar todos os dias
    document.getElementById('todosDias').addEventListener('change', function() {
        var checkboxes = document.querySelectorAll('input[name="dia_semana[]"]');
        checkboxes.forEach(function(checkbox) {
            checkbox.checked = document.getElementById('todosDias').checked;
        });
    });
</script>
