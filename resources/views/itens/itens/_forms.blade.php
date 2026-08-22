
<style>
    .form-wrapper {
        background-color: #ffffff;
        padding: 40px;
        border-radius: 15px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        margin-top: 50px;
    }

    label span {
        color: red;
        margin-right: 5px;
    }

    .btn-success {
        background-color: #28a745;
        border: none;
        font-weight: bold;
        padding: 12px 30px;
        border-radius: 10px;
    }

    .btn-success:hover {
        background-color: #218838;
    }

    .form-control,
    .form-control-file,
    select,
    textarea {
        border-radius: 8px;
    }

    textarea {
        resize: vertical;
    }
</style>

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
                    <input type="file" class="form-control" name="img_item" id="img_item" required>
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
                    <textarea class="form-control" name="descricao_item" rows="3" placeholder="Escreva uma descrição..." required></textarea>
                </div>

                <!-- Quantidade -->
                <div class="col-md-6">
                    <label for="qd"><span>*</span>Quantidade</label>
                    <input type="number" class="form-control" name="qd" placeholder="Quantidade do item" required>
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
