@extends('default.layout', ['title' => 'MEUS ITENS'])

@section('content')
    <div class="page-content">
        <div class="card">
            <div class="card-body p-4">
                <div class="container">
                    <h2 class="mb-4">Itens Cadastrados</h2>

                    @if (session('flash_sucesso'))
                        <div class="alert alert-success">{{ session('flash_sucesso') }}</div>
                    @endif

                    @if (session('flash_erro'))
                        <div class="alert alert-danger">{{ session('flash_erro') }}</div>
                    @endif

                    <div class="mb-3">
                        <a href="{{ route('itens.create') }}" class="btn btn-primary">Novo Item</a>
                    </div>

                    <form action="{{ route('itens.index') }}" method="GET" class="mb-3">
                        <div class="input-group">
                            <input type="text" name="nome_item" class="form-control" placeholder="Buscar por nome..." value="{{ request()->nome_item }}">
                            <button type="submit" class="btn btn-outline-secondary">Buscar</button>
                        </div>
                    </form>

                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Imagem</th>
                                <th>Nome</th>
                                <th>Preço</th>
                                <th>Descrição</th>
                               <th>Status</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($data as $item)
                                <tr>
                                    <td>
                                        @php $imagePath = public_path($item->img_item); @endphp
                                        @if ($item->img_item && file_exists($imagePath))
                                            <img src="{{ asset($item->img_item) }}" width="60" height="60">
                                        @else
                                            <img src="https://deliveryba.com.br/uploads/{{ $item->img_item }}" width="60" height="60">
                                        @endif
                                    </td>
                                    <td>{{ $item->nome_item }}</td>
                                    <td>R$ {{ number_format($item->preco_item, 2, ',', '.') }}</td>
                                    <td>{{ $item->descricao_item }}</td>
                                     <td>
                                        <form action="{{ route('itens.atualizar-disponibilidade') }}" method="POST" style="display: inline;">
                                            @csrf
                                            <input type="hidden" name="iditem" value="{{ $item->id }}">
                                            <button type="submit" class="btn btn-sm {{ $item->disponivel ? 'btn-success' : 'btn-secondary' }}">
                                                {{ $item->disponivel ? 'Ativo' : 'Desativado' }}
                                            </button>
                                        </form>
                                    </td>
                                    <td>
                                        <a href="{{ route('itens.edit', $item->id) }}" class="btn btn-sm btn-warning">Editar</a>
                                         <form action="{{ route('itens.destroy', $item->id) }}" method="POST" class="form-excluir" style="display: inline;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger">Excluir</button>
                                        </form>
                                    </td>
                                   
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6">Nenhum item encontrado.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>

                    <div class="d-flex justify-content-center">
                        {!! $data->appends(request()->query())->links() !!}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')


<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    $(document).ready(function () {
        // Confirmação de exclusão com SweetAlert2
        $('.form-excluir').on('submit', function (e) {
            e.preventDefault(); // impede o envio automático
            const form = this;

            Swal.fire({
                title: 'Tem certeza?',
                text: "Você não poderá reverter essa ação!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Sim, excluir!',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit(); // envia o formulário
                }
            });
        });

        // Alternar disponibilidade (já existente, mantido como está)
        $('.toggle-disponibilidade').on('click', function () {
            const botao = $(this);
            const idDoItem = botao.data('id');

            $.ajax({
                type: 'POST',
                url: '{{ route("itens.atualizar-disponibilidade") }}',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                data: {
                    iditem: idDoItem
                },
                success: function (response) {
                    if (response.success) {
                        const novoStatus = response.disponivel ? 'Ativo' : 'Desativado';
                        botao.text(novoStatus);
                        botao.toggleClass('btn-success btn-secondary');
                    } else {
                        alert("Erro: " + response.message);
                    }
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    console.log("Erro no Ajax:", jqXHR, textStatus, errorThrown);
                    alert("Erro ao atualizar status.");
                }
            });
        });
    });
</script>
@endsection
