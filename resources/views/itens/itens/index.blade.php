@extends('default.layout',['title' => 'MEUS ITENS'])

@section('content')
<meta name="csrf-token" content="{{ csrf_token() }}">

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

    <form action="{{ route('itens.index') }}" method="GET">
        <div class="input-group mb-3">
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
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            @forelse($data as $item)
                <tr>
                    <td>
                    @php
        // Caminho completo para o arquivo de imagem no diretório público
        $imagePath = public_path($item->img_item);
    @endphp

    @if ($item->img_item && file_exists($imagePath))
        <img src="{{ asset($item->img_item) }}" width="60" height="60">
    @else
    <img src="https://deliveryba.com.br/uploads/{{ $item->img_item }}" width="60" height="60">  <!-- Caminho para uma imagem padrão -->

                        @endif
                    </td>
                    <td>{{ $item->nome_item }}</td>
                    <td>R$ {{ number_format($item->preco_item, 2, ',', '.') }}</td>
                    <td>{{ $item->descricao_item }}</td>
                    <td>
                        <!-- Editar -->
                        <a href="{{ route('itens.edit', $item->id) }}" class="btn btn-sm btn-warning">Editar</a>
                        
                        <!-- Excluir -->
                        <form action="{{ route('itens.destroy', $item->id) }}" method="POST" style="display: inline;" onsubmit="return confirm('Tem certeza que deseja excluir?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger">Excluir</button>
                        </form>
                    </td>
                    <td>
                        <!-- Checkbox para atualizar disponibilidade -->
                        <div class="ckbx-style-14">
                            <input type="checkbox" value="{{ $item->id }}" id="atualizar_{{ $item->id }}" name="ckbx-style-14" {{ $item->disponivel ? 'checked' : '' }}>
                            <label class="atualizar_{{ $item->id }}" for="atualizar_{{ $item->id }}"></label>
                        </div>                  

                       <script type="text/javascript">
  $(document).ready(function(){
    // Adiciona o CSRF token ao header das requisições AJAX
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    // Evento de clique no checkbox
    $('.atualizar_{{ $item->id }}').click(function(){
        var idDoItem = $('#atualizar_{{ $item->id }}').val();
        var disponibilidade = $('#atualizar_{{ $item->id }}').is(':checked') ? 1 : 0; // Verifica se o checkbox está marcado

        $.ajax({
            url: '{{ route("itens.disponibilidade") }}', // URL da rota no Laravel
            method: 'POST',
            data: {
                iditem: idDoItem,
                disponibilidade: disponibilidade, // Envia a disponibilidade (1 ou 0)
            },
            success: function(data) {
                if (data.success) {
                    // Atualiza o estado do checkbox com base na resposta
                    var checkbox = $('#atualizar_{{ $item->id }}');
                    checkbox.prop('checked', data.disponivel); // Atualiza o estado do checkbox com o novo valor de disponibilidade
                } else {
                    alert('Erro ao atualizar disponibilidade: ' + data.message);
                }
            },
            error: function(xhr, status, error) {
                alert('Ocorreu um erro na requisição AJAX.');
            }
        });
    });
});

</script>

                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5">Nenhum item encontrado.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="d-flex justify-content-center">
        {!! $data->appends(request()->query())->links() !!}
    </div>
</div>
@endsection
