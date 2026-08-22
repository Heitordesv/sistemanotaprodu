<div class="container">
    <form method="POST" action="{{ route('itens.store') }}" enctype="multipart/form-data">
        @csrf
        <div class="form-wrapper">
            <h4 class="mb-4 text-center fw-bold">Adicionar Novo Item</h4>

            <div class="row g-3">
                <!-- Categoria -->
                <div class="col-md-6">
                    <label for="id_cat"><span>*</span>Categoria</label>
                    <select name="id_cat" class="form-control" required>
                        <option value="">Selecione a categoria</option>
                        @foreach ($categorias as $categoria)
                            <option value="{{ $categoria->id }}">
                                {{ $categoria->nome_cat }} - {{ $categoria->desc_cat }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Imagem -->
                <div class="col-md-6">
                    <label for="img_item"><span>*</span>Imagem do Item</label>
                    <input type="file" class="form-control" name="img_item" id="img_item" >
                </div>

                <!-- Nome -->
                <div class="col-md-6">
                    <label for="nome_item"><span>*</span>Nome do Item</label>
                    <input type="text" class="form-control" name="nome_item" placeholder="Nome do item" required>
                </div>

                <!-- Preço -->
                <div class="col-md-6">
                    <label for="preco_item"><span>*</span>Preço Base</label>
                    <input type="text" class="form-control" name="preco_item" placeholder="R$ 00,00" required data-mask="#.##0,00" data-mask-reverse="true" maxlength="11">
                </div>

                <!-- Descrição -->
                <div class="col-12">
                    <label for="descricao_item"><span>*</span>Descrição do Item</label>
                    <textarea class="form-control" name="descricao_item" rows="3" placeholder="Escreva uma descrição..." ></textarea>
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

                <!-- Quantidade -->
                <div class="col-md-6">
                    <label for="qd"><span>*</span>Quantidade</label>
                    <input type="number" class="form-control" name="qd" placeholder="Quantidade do item" required>
                </div>

  <!-- posicao -->
                <div class="col-md-6">
                    <label for="posicao"><span>*</span>Posicao</label>
                    <input type="number" class="form-control" name="posicao" placeholder="posicao do item" required>
                </div>
                <!-- Campo oculto -->
                <input type="hidden" name="disponivel" value="1">

                <!-- Botão -->
                <div class="col-12 mt-4">
                    <button type="submit" class="btn btn-success w-100">Adicionar Item</button>
                </div>
            </div>
        </div>
    </form>
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
