@extends('default.layout',['title' => 'Lista de Erros'])
@section('content')

@if(isSuper(session('user_logged')['super']))

<div class="page-content">
    <div class="card">
        <div class="card-body p-4">

            <div class="col">
                {!! Form::open()->fill(request()->all())->get() !!}
                <div class="row mt-2">
                    <div class="col-md-5">
                        {!! Form::select('empresa_id', 'Empresa', ['' => 'Selecione'] + $empresas->pluck('nome', 'id')->all())->attrs(['class' => 'form-select']) !!}
                    </div>
                    <div class="col-md-3">
                        {!! Form::date('start_date', 'Data Inicial') !!}
                    </div>
                    <div class="col-md-3">
                        {!! Form::date('end_date', 'Data Final') !!}
                    </div>
                    <div class="col-md-3 text-left">
                        <br>
                        <button class="btn btn-primary" type="submit">
                            <i class="bx bx-search"></i> Pesquisar
                        </button>
                        <a id="clear-filter" class="btn btn-danger" href="{{ route('errosLog.index') }}">
                            <i class="bx bx-eraser"></i> Limpar
                        </a>
                    </div>
                </div>
                {!! Form::close() !!}

                <hr />

                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive tbl-400">
                            <table class="table mb-0 table-striped">
                                <thead>
                                    <tr>
                                        <th>Id</th>
                                        <th>Data</th>
                                        <th>Empresa</th>
                                        <th>Arquivo</th>
                                        <th>Linha</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($data as $item)
                                    <tr>
                                        <td>{{ $item->id }}</td>
                                        <td>{{ $item->created_at }}</td>
                                        <td>{{ $item->empresa->razao_social }}</td>
                                        <td>{{ $item->arquivo }}</td>
                                        <td>{{ $item->linha }}</td>
                                        <td class="d-flex">

                                            {{-- BOTÃO MODAL --}}
                                            <button class="btn btn-info btn-sm me-1" data-bs-toggle="modal" data-bs-target="#modalErro{{$item->id}}">
                                                <i class="bx bx-show"></i>
                                            </button>

                                            {{-- FORM DELETE --}}
                                            <form action="{{ route('errosLog.destroy', $item->id) }}" method="post" id="form-{{$item->id}}">
                                                @method('delete')
                                                @csrf
                                                <button type="button" class="btn btn-danger btn-sm btn-delete">
                                                    <i class="bx bx-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>

                                    {{-- MODAL COMPLETO --}}
                                    <div class="modal fade" id="modalErro{{$item->id}}" tabindex="-1">
                                        <div class="modal-dialog modal-lg">
                                            <div class="modal-content">
                                                <div class="modal-header bg-dark text-white">
                                                    <h5 class="modal-title">Detalhes do Erro #{{ $item->id }}</h5>
                                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <strong>Empresa:</strong> {{ $item->empresa->razao_social }} <br>
                                                    <strong>Arquivo:</strong> {{ $item->arquivo }} <br>
                                                    <strong>Linha:</strong> {{ $item->linha }} <br>
                                                    <hr>
                                                    <strong>Mensagem completa:</strong>
                                                    <pre style="background:#222;color:#0f0;padding:15px;border-radius:5px;white-space:pre-wrap;">
{!! print_r($item->erro, true) !!}
                                                    </pre>
                                                </div>
                                                <div class="modal-footer">
                                                    <button class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    @empty
                                    <tr>
                                        <td colspan="6" class="text-center">Nada encontrado</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                {!! $data->appends(request()->all())->links() !!}
            </div>

        </div>
    </div>
</div>

{{-- SCRIPT DELETE --}}
<script>
    document.querySelectorAll('.btn-delete').forEach(btn => {
        btn.addEventListener('click', function() {
            if (confirm('Deseja realmente excluir este registro?')) {
                this.closest('form').submit();
            }
        });
    });
</script>

@else
<div class="alert alert-danger text-center mt-4">
    <h4>Acesso Negado</h4>
    <p>Você não possui permissão para acessar esta página. Apenas administradores podem visualizar e gerenciar os logs de erro.</p>
</div>
@endif

@endsection
