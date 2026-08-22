@extends('default.layout', ['title' => 'Categorias '])

@section('content')
<div class="page-content">
    <div class="card">
        <div class="card-body p-4">
            <div class="page-breadcrumb d-sm-flex align-items-center mb-3">
                <div class="ms-auto">
                    <a href="{{ route('wscat.create') }}" type="button" class="btn btn-success">
                        <i class="bx bx-plus"></i> Nova categoria
                    </a>
                </div>
            </div>
            <div class="col">
                <h6 class="mb-0 text-uppercase">Categorias</h6>
                {!! Form::open()->fill(request()->all())->get() !!}
                <div class="row">
                    <div class="col-md-3">
                        {!! Form::text('nome_cat', 'Pesquisar por nome') !!}
                    </div>
                    <div class="col-md-3 text-left">
                        <br>
                        <button class="btn btn-primary" type="submit"><i class="bx bx-search"></i> Pesquisar</button>
                        <a id="clear-filter" class="btn btn-danger" href="{{ route('wscat.index') }}"><i class="bx bx-eraser"></i> Limpar</a>
                    </div>
                </div>
                {!! Form::close() !!}
                <hr />
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table mb-0 table-striped">
                                <thead>
                                    <tr>
                                    <th width="50%"></th>
                                        <th width="50%">Nome</th>
                                        <th width="20%">Horário de Abertura</th>
                                        <th width="20%">Horário de Fechamento</th>
                                        <th width="20%">Dias semana</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($data as $item)
                                    <tr>
<!--<td>
    @if (!empty($item->icon_cat))
        <img src="{{ asset($item->icon_cat) }}" alt="Ícone" width="50" height="50">
    @else
        <span>Sem imagem</span>
    @endif
</td>-->
                                        <td>{{ $item->nome_cat }}</td>
                                        <td>{{ $item->hora_abertura }}</td>
                                        <td>{{ $item->hora_fechamento }}</td>
                                                                                <td>{{ $item->dias_semana }}</td>

                                        <td>
                                          
                                                <a href="{{ route('wscat.edit', $item) }}" class="btn btn-warning btn-sm text-white">
                                                    <i class="bx bx-edit"></i>
                                                </a>
                                                <form action="{{ route('wscat.destroy', $item->id) }}" method="POST" class="form-excluir" style="display: inline;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger">Excluir</button>
                                        </form>
                                            </form>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="4" class="text-center">Nada encontrado</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            {!! $data->appends(request()->all())->links() !!}
        </div>
    </div>
</div>
@endsection
