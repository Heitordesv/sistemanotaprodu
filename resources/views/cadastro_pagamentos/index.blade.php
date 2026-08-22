@extends('default.layout', ['title' => 'Formas de Pagamento'])

@section('content')
<div class="page-content">
    <div class="card">
        <div class="card-body p-4">
            <!-- Breadcrumb e Botão de Nova Forma de Pagamento -->
            <div class="page-breadcrumb d-sm-flex align-items-center mb-3">
                <div class="ms-auto">
                    <a href="{{ route('cadastro_pagamentos.create') }}" type="button" class="btn btn-success">
                        <i class="bx bx-plus"></i> Nova forma de pagamento
                    </a>
                </div>
            </div>

            <!-- Título da Página -->
            <div class="col">
         

                <!-- Tabela de Formas de Pagamento -->
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table mb-0 table-striped">
                                <thead>
                                    <tr>
                                        <th width="75%">Forma de Pagamento</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($data as $item)
                                    <tr>
                                        <td>{{ $item->f_pagamento }}</td>
                                        <td>
                                            <!-- Botão de Edição -->
                                            <a href="{{ route('cadastro_pagamentos.edit', $item->id_f_pagamento) }}" class="btn btn-warning btn-sm text-white">
                                                <i class="bx bx-edit"></i>
                                            </a>
                                            
                                            <!-- Formulário de Exclusão -->
                                            <form action="{{ route('cadastro_pagamentos.destroy', $item->id_f_pagamento) }}" method="post" id="form-{{ $item->id_f_pagamento }}" style="display:inline;">
                                                @csrf
                                                @method('delete')
                                                <button type="button" class="btn btn-danger btn-sm" onclick="confirmDelete({{ $item->id_f_pagamento }})">
                                                    <i class="bx bx-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="2" class="text-center">Nada encontrado</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Paginação -->
                {!! $data->appends(request()->all())->links() !!}
            </div>
        </div>
    </div>
</div>

<script>
    // Função de confirmação de exclusão
    function confirmDelete(id) {
        if (confirm('Você tem certeza que deseja excluir esta forma de pagamento?')) {
            document.getElementById('form-' + id).submit();
        }
    }
</script>
@endsection
