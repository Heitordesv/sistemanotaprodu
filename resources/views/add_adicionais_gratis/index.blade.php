@extends('default.layout', ['title' => 'Categorias Delivery'])

@section('content')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
    .btn-info {
        font-weight: bold;
        border-radius: 5px;
        background-color: #17a2b8;
        color: white;
        transition: background-color 0.3s ease;
    }

    .btn-info:hover {
        background-color: #138496;
    }

    .list-group-item {
        border-radius: 5px;
        padding: 10px;
        background-color: #f8f9fa;
        transition: background-color 0.3s ease, color 0.3s ease;
    }

    .list-group-item:hover {
        background-color: #e2e6ea;
        cursor: pointer;
        color: #007bff;
    }

    #id_cat {
        display: none;
    }

    #collapse_listCategorias {
        margin-top: 15px;
    }

    small.text-muted {
        font-size: 0.875rem;
        font-style: italic;
    }

    @media (max-width: 767px) {
        .btn-info {
            font-size: 14px;
        }

        .list-group-item {
            font-size: 14px;
        }
    }
</style>
<div class="page-content">
    <div class="card">
        <div class="card-body p-4">
            <div class="page-breadcrumb d-sm-flex align-items-center mb-3">
                <div class="ms-auto">
                    
                </div>
            </div>

<div class="container margin_60">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div id="success"></div>
            <br><br>
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif

            <div class="indent_title_in">
                <i class="icon-plus-squared"></i>
                <h3><strong>ADICIONAIS E COMPLEMENTOS GRATIS</strong></h3>
                <p><b>Adicione adicionais e complementos para os itens.</b></p>
            </div>

            <form action="{{ route('add_adicionais_gratis.store') }}" method="POST" id="formaddadicional">
                @csrf

                <div class="row">
                    <div class="col-md-6">
                        <label for="nome_adicional_gratis">Nome</label>
                        <input type="text" name="nome_adicional_gratis" class="form-control" required placeholder="Ex: Bacon, Ovo">
                    </div>
                   
                </div>

                <!-- <br>

                <label>Medida</label><br>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="medida_adicional" value="UN" checked>
                    <label class="form-check-label">UN</label>
                </div>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="medida_adicional" value="KG">
                    <label class="form-check-label">KG</label>
                </div>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="medida_adicional" value="LT">
                    <label class="form-check-label">LT</label>
                </div> -->

                <br><br>

                <label for="categorias_adicional">Categorias</label>
                <div class="list-group" id="categoriasList">
                    @forelse($categorias as $cat)
                        <a href="#" class="list-group-item list-group-item-action" data-id="{{ $cat->id }}" data-nome="{{ $cat->nome_cat }}">{{ $cat->nome_cat }}</a>
                    @empty
                        <a href="/categoria" class="list-group-item list-group-item-warning">Cadastre novas categorias!</a>
                    @endforelse
                </div>
                <input type="hidden" id="categorias_adicional" name="categorias_adicional">
                <br><br>

                <!-- Área para exibir os itens da categoria selecionada -->
                <div id="itens_categoria" style="display:none;">
                    <label for="itens_adicional">Itens relacionados</label>
                    <div id="itens_list" class="list-group">
                        <!-- Itens serão carregados aqui via AJAX -->
                    </div>
                    <input type="hidden" id="itens_adicional" name="itens_adicional">
                </div>

                <br>

                <button type="submit" class="btn btn-primary w-100">Cadastrar</button>
            </form>
        </div>
    </div>
    <div class="row justify-content-center">
    <div class="col-md-10">
        {{-- Exibindo os Complementos Adicionais --}}
        <div class="complementos-adicionais mt-5">
            <h3 class="mb-4">Complementos Adicionais</h3>
            <table class="table table-bordered table-striped table-hover">
                <thead class="thead-dark">
                    <tr>
                        <th>ID</th>
                        <th>Categorias vinculadas</th>
                        <th>Nome</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($coplementoAdd as $adicional)
                    <tr>
                        <td>{{ $adicional->id }}</td>
                        <td>{{ $adicional->categoria->nome_cat }}</td>
                        <td>{{ $adicional->nome_adicional_gratis }}</td>
                          {{-- Botões de Ação (Excluir e Visualizar) --}}
  <td>
<form action="{{ route('add_adicionais_gratis.destroy', $adicional->id) }}" method="POST" id="form-{{ $adicional->id }}" style="display: inline-block;">
    @csrf
    @method('DELETE')
    <button type="button" class="btn btn-sm btn-danger" onclick="confirmarExclusao('{{ $adicional->id }}')">
        <i class="bx bx-trash"></i> Excluir
    </button>
</form>

</td>
  <td>

<form action="{{ route('adicional.atualizar-disponibilidade') }}" method="POST" style="display: inline;">
    @csrf
    <input type="hidden" name="id_adicional_gratis" value="{{ $adicional->id }}">
    <button type="submit" class="btn btn-sm {{ $adicional->status_adicional_gratis ? 'btn-success' : 'btn-secondary' }}">
        {{ $adicional->status_adicional_gratis ? 'Ativo' : 'Desativado' }}
    </button>
</form>
</td>

                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
</div>
</div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    function confirmarExclusao(id) {
        Swal.fire({
            title: 'Tem certeza que deseja excluir?',
            text: "Essa ação é irreversível!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Sim, excluir',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('form-' + id).submit();
            }
        });
    }
</script>


<script>
// Quando uma categoria é selecionada
$(document).ready(function () {
    const categoriasSelecionadas = [];
    const itensSelecionados = [];

    // Seleção de categorias
    $(document).on('click', '#categoriasList .list-group-item', function (event) {
        event.preventDefault();

        const categoriaId = $(this).data('id');
        const index = categoriasSelecionadas.indexOf(categoriaId);

        if (index === -1) {
            categoriasSelecionadas.push(categoriaId);
            $(this).addClass('active');
        } else {
            categoriasSelecionadas.splice(index, 1);
            $(this).removeClass('active');
        }

        $('#categorias_adicional').val(categoriasSelecionadas.join(','));

        if (categoriasSelecionadas.length > 0) {
            $('#itens_categoria').show();
            fetchItensPorCategorias(categoriasSelecionadas);
        } else {
            $('#itens_categoria').hide();
            $('#itens_list').empty();
        }
    });

    // Seleção de itens
    $(document).on('click', '#itens_list .list-group-item', function (event) {
        event.preventDefault();

        const itemId = $(this).data('id');
        const index = itensSelecionados.indexOf(itemId);

        if (index === -1) {
            itensSelecionados.push(itemId);
            $(this).addClass('active');
        } else {
            itensSelecionados.splice(index, 1);
            $(this).removeClass('active');
        }

        $('#itens_adicional').val(itensSelecionados.join(','));
    });

    // Carregar itens por categorias selecionadas
    function fetchItensPorCategorias(categorias) {
        let itemsList = $('#itens_list');
        itemsList.html('<a href="#" class="list-group-item list-group-item-warning">Carregando itens...</a>');

        $.ajax({
            url: "{{ route('add_adicionais_gratis.getItensPorCategoria') }}",
            method: 'POST',
            data: {
                categorias: categorias,
                _token: "{{ csrf_token() }}"
            },
            success: function (data) {
                itemsList.empty();
                itensSelecionados.length = 0;
                $('#itens_adicional').val('');

                if (data.length > 0) {
                    $.each(data, function (index, item) {
                        let listItem = $('<a>')
                            .addClass('list-group-item list-group-item-action')
                            .text(item.name_adicionais_cat + ' - ' + item.item.nome_item)
                            .attr('data-id', item.id)
                            .attr('data-id_itens', item.id_itens)
                            .attr('data-id_adicionais_cat', item.id_adicionais_cat)
                            .attr('href', '#');
                        itemsList.append(listItem);
                    });
                } else {
                    itemsList.html('<a href="#" class="list-group-item list-group-item-warning">Nenhum item encontrado para estas categorias!</a>');
                }
            },
            error: function () {
                itemsList.html('<a href="#" class="list-group-item list-group-item-warning">Erro ao carregar os itens.</a>');
            }
        });
    }
});


 function select_listItens(id) {
        const item = $(`#item_${id}`);
        if (item.hasClass('active')) {
            item.removeClass('active');
            const current = $("#id_itens").val().split(',').filter(e => e && e !== id);
            $("#id_itens").val(current.join(',') + (current.length ? ',' : ''));
        } else {
            item.addClass('active');
            $("#id_itens").val($("#id_itens").val() + id + ',');
        }
    }

</script>

@endsection
