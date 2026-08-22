@extends('default.layout', ['title' => 'CATEGORIAS DE COMPLEMENTOS'])

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

            <div id="sendnewpass" class="indent_title_in">
                <i class="icon-plus-squared"></i>
                <h3><strong>CATEGORIAS DE COMPLEMENTOS</strong></h3>
                <p><b>Adicione as categorias dos complementos grátis e pagos.</b></p>
                <br />

                <form method="post" action="{{ route('adicionar_condicao_adicional.store') }}">
                    @csrf
                    <div class="row">
                        <!-- Nome da Categoria -->
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name_adicionais_cat">Nome da Categoria</label>
                                <input required type="text" name="name_adicionais_cat" id="name_adicionais_cat" 
                                    class="form-control" placeholder="Ex: Frutas, Carnes, Coberturas..."
                                    value="{{ old('name_adicionais_cat') }}" />
                                <small class="form-text text-muted">Nome da categoria de complementos.</small>
                            </div>
                        </div>

                        <!-- Quantidade -->
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="amount">Quantidade</label>
                                <input required type="number" name="amount" id="amount" class="form-control"
                                    placeholder="Utilize -1 para sem restrições" min="-1" 
                                    value="{{ old('amount') }}" />
                                <small class="form-text text-muted">Quantidade de complementos disponíveis.</small>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <!-- Tipo do Complemento -->
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="pay">Tipo de Complemento</label>
                                <select class="form-control" name="pay" id="pay">
                                    <option value="0" {{ old('pay') == '0' ? 'selected' : '' }}>Grátis</option>
                                    <option value="1" {{ old('pay') == '1' ? 'selected' : '' }}>Pago</option>
                                </select>
                                <small class="form-text text-muted">Tipo de custo do complemento.</small>
                            </div>
                        </div>
                    </div>
                    <!-- Categoria Vinculante -->
                    <div class="row">
                        <div class="col-12">
                            <button class="btn btn-info w-100" type="button" data-toggle="collapse"
                                data-target="#collapse_listCategorias">
                                Selecionar Categoria Vinculante
                            </button>
                        </div>
                    </div>
                    <div id="collapse_listCategorias" class="collapse mt-2">
                        <div class="list-group" id="listCategorias">
                            <label for="id_cat">Categoria</label>
                            @foreach ($categorias as $cat)
                                <a id="{{ $cat->id }}" class="list-group-item list-group-item-action"
                                   style="cursor: pointer;">
                                    {{ $cat->nome_cat }}
                                </a>
                            @endforeach
                            <input type="hidden" id="id_cat" name="id_cat" value="{{ old('id_cat') }}">
                            <small class="form-text text-muted">Selecione a categoria para vincular.</small>
                        </div>
                    </div>

                    <!-- Item Vinculante -->
                    <div class="row mt-3">
                        <div class="col-12">
                            <button id="btnListItens" class="btn btn-info w-100" type="button">
                                Selecionar Item Vinculante
                            </button>
                        </div>
                    </div>

                    <div id="collapse_listItens" class="collapse mt-2">
                        <div class="list-group" id="listItens">
                            <label for="id_itens">Itens</label>
                            <div id="content_listItens"></div>
                            <input type="hidden" id="id_itens" name="id_itens" value="">
                            <small class="form-text text-muted">Selecione o item para vincular.</small>
                        </div>
                    </div>

                    <!-- Botão Cadastrar -->
                    <div class="mt-4">
                        <button class="btn btn-primary w-100" type="submit">Cadastrar</button>
                    </div>
                </form>
            </div>
        </div>
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
                        <th>Item</th>
                        <th>Nome</th>
                        <th>Condição</th>
                        <th>Pagar</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($coplementoAdd as $adicional)
                    <tr>
                        <td>{{ $adicional->id }}</td>
<td>
    {{ optional(optional($adicional->Item)->categoria)->nome_cat ?? 'Sem categoria' }}
    - {{ optional($adicional->Item)->nome_item ?? 'Sem item' }}
</td>
                        <td>{{ $adicional->name_adicionais_cat }}</td>
<td>{{ intval($adicional->amount) }}</td>
                        <td>{{ $adicional->pay ? 'Pago' : 'Grátis' }}</td>
                         <td>
   <form action="{{ route('adicionar_condicao_adicional.destroy', $adicional->id) }}" method="POST" id="form-{{ $adicional->id }}" style="display: inline-block;">
    @csrf
    @method('DELETE')
    <button type="button" class="btn btn-sm btn-danger" onclick="confirmarExclusao('{{ $adicional->id }}')">
        <i class="bx bx-trash"></i> Excluir
    </button>
</form>

</td>       
                    @endforeach
                                  </tr>

                </tbody>
            </table>
                   <div class="d-flex justify-content-center mt-4">
    {{ $coplementoAdd->links() }}
</div>
        </div>
    </div>
        </div>

</div>
<br>
@endsection
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.js" integrity="sha256-H+K7U5CnXl1h5ywQfKtSj8PCmoN9aaq30gDh27Xc0jk=" crossorigin="anonymous"></script>
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
    $(document).ready(function() {
        // Seleção de Categorias
        $('#listCategorias a').on('click', function() {
            const id = $(this).attr('id');
            if ($(this).hasClass('active')) {
                $(this).removeClass('active');
                const current = $("#id_cat").val().split(',').filter(e => e && e !== id);
                $("#id_cat").val(current.join(',') + (current.length ? ',' : ''));
            } else {
                $(this).addClass('active');
                $("#id_cat").val($("#id_cat").val() + id + ',');
            }
        });

        // Ao clicar no botão "Selecionar Item Vinculante"
        $('#btnListItens').on('click', function() {
            listItens();
        });
    });

    // 🟢 Função precisa estar no escopo global
    function listItens() {
        const categories = [];
        $('#listCategorias a.active').each(function() {
            categories.push($(this).attr("id"));
        });

        if (categories.length > 0) {
            $.ajax({
                url: "{{ route('adicionar_condicao_adicional.getItensPorCategoria') }}",
                type: "POST",
                data: {
                    categories: categories,
                    _token: "{{ csrf_token() }}"
                },
                success: function(response) {
                    $("#content_listItens").empty();

                    if (response.items.length > 0) {
                        response.items.forEach(function(item) {
                            $("#content_listItens").append(`
                                <a id="item_${item.id}" class="list-group-item list-group-item-action"
                                   style="cursor:pointer;" onclick="select_listItens('${item.id}')">
                                    ${item.nome_item}
                                </a>
                            `);
                        });
                    } else {
                        $("#content_listItens").append('<div class="text-muted">Nenhum item encontrado.</div>');
                    }

                    $('#collapse_listItens').addClass('show');
                },
                error: function(xhr) {
                    alert('Erro ao carregar itens!');
                }
            });
        } else {
            alert('Selecione ao menos uma categoria.');
        }
    }

    // Seleção de Itens
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
    