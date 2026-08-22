<form method="POST"
      action="{{ isset($item) ? route('itens.update', $item->id) : route('itens.store') }}"
      enctype="multipart/form-data">
    
    @csrf
    @if(isset($item))
        @method('PUT')
    @endif

    <div class="wrapper_indent">

        <!-- Categoria -->
        <div class="form-group">
            <label for="id_cat"><span style="color: red;">*</span> CATEGORIA</label>        
            <select name="id_cat" class="form-control" required>
                <option value="">Selecione a categoria</option>
                @foreach ($categorias as $categoria)
                    <option value="{{ $categoria->id }}"
                        {{ (isset($item) && $item->id_cat == $categoria->id) ? 'selected' : '' }}>
                        {{ $categoria->nome_cat }} - {{ $categoria->desc_cat }}
                    </option>
                @endforeach
            </select>
        </div>

        <!-- Imagem -->
        <div class="form-group">
            <label for="img_item"><span style="color: red;">*</span> Imagem do Item</label>
            <input type="file" class="form-control-file" name="img_item" id="img_item" {{ isset($item) ? '' : 'required' }}>
            @if(isset($item) && $item->img_item)
                <div class="mt-2">
                    <img src="{{ asset($item->img_item) }}" width="80" height="80" alt="Imagem atual">
                </div>
            @endif
        </div>

        <!-- Nome -->
        <div class="form-group">
            <label for="nome_item"><span style="color: red;">*</span> NOME:</label>
            <input type="text" class="form-control" name="nome_item" placeholder="Nome do item" required
                   value="{{ old('nome_item', $item->nome_item ?? '') }}">
        </div>

        <!-- Preço -->
        <div class="form-group">
            <label for="preco_item"><span style="color: red;">*</span> PREÇO BASE DO ITEM:</label>
            <input type="text" class="form-control" name="preco_item" placeholder="R$ 00,00" required
                   data-mask="#.##0,00" data-mask-reverse="true" maxlength="11"
                   value="{{ old('preco_item', $item->preco_item ?? '') }}">
        </div>

        <!-- Descrição -->
        <div class="form-group">
            <label for="descricao_item"><span style="color: red;">*</span> DESCRIÇÃO:</label>
            <textarea class="form-control" name="descricao_item" rows="3" placeholder="Descrição do item..." required>{{ old('descricao_item', $item->descricao_item ?? '') }}</textarea>
        </div>

        <!-- Dias da Semana -->
        <div class="form-group">
            <label><span style="color: red;">*</span> Dias em que o item aparece para o cliente:</label>
            <div class="radio-group">
          @php
    $diasSemana = ['Domingo', 'Segunda', 'Terca', 'Quarta', 'Quinta', 'Sexta', 'Sabado'];
    $diaSemanaRaw = old('dia_semana', isset($item) ? $item->dia_semana : []);
    $diasSelecionados = is_array($diaSemanaRaw) ? $diaSemanaRaw : explode(',', $diaSemanaRaw);
@endphp

@foreach ($diasSemana as $dia)
    <div class="form-check">
        <input class="form-check-input"
               type="checkbox"
               name="dia_semana[]"
               value="{{ $dia }}"
               id="check_{{ $dia }}"
               {{ in_array($dia, $diasSelecionados) ? 'checked' : '' }}>
        <label class="form-check-label" for="check_{{ $dia }}">
            {{ $dia }}
        </label>
    </div>
@endforeach


        <!-- Quantidade -->
        <div class="form-group">
            <label for="qd"><span style="color: red;">*</span> Quantidade:</label>
            <input type="number" class="form-control" name="qd" placeholder="Quantidade do item" required
                   value="{{ old('qd', $item->qd ?? '') }}">
        </div>
        
           <!-- Quantidade -->
        <div class="form-group">
            <label for="posicao"><span style="color: red;">*</span> posicao:</label>
            <input type="number" class="form-control" name="posicao" placeholder="posicao" required
                   value="{{ old('posicao', $item->posicao ?? '') }}">
        </div>

        <!-- Campo oculto de disponibilidade -->
        <input type="hidden" name="disponivel" value="1">

        <!-- Botão -->
        <div class="form-group">
            <button type="submit" class="btn btn-success mt-3 w-100">
                {{ isset($item) ? 'Atualizar Item' : 'Adicionar Item' }}
            </button>
        </div>
    </div>
</form>

<script>
    document.getElementById('todosDias').addEventListener('change', function () {
        const checkboxes = document.querySelectorAll('input[name="dia_semana[]"]');
        checkboxes.forEach(cb => cb.checked = this.checked);
    });
</script>
