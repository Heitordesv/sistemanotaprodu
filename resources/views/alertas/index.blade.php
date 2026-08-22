@extends('default.layout',['title' => 'Alertas'])
@section('content')
<div class="page-content">
    <div class="card ">
        <div class="card-body p-4">
            <div class="page-breadcrumb d-sm-flex align-items-center mb-3">

                <div class="ms-auto">
                    <a href="{{ route('alertas.create')}}" type="button" class="btn btn-success">
                        <i class="bx bx-plus"></i> Novo alerta
                    </a>
                </div>
            </div>
            <div class="col">
                <h6 class="mb-0 text-uppercase">Alertas para empresas</h6>
                <hr />
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table mb-0 table-striped">
                                <thead class="">
                                    <tr>
                                        <th>Id</th>
                                        <th>Título</th>
                                        <th>Status</th>
                                        <th>Prioridade</th>
                                        <th>Data de cadastro</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($data as $item)
                                    <tr>
                                        <td>{{ $item->id }}</td>
                                        <td>{{ $item->titulo }}</td>
                                        <td>
                                            @if($item->status)
                                            <span class="btn btn-success btn-sm">Ativo</span>
                                            @else
                                            <span class="btn btn-warning btn-sm">Desativado</span>
                                            @endif
                                        </td>
                                        <td>{{ strtoupper($item->prioridade) }}</td>
                                        <td>{{ $item->created_at }}</td>    
                                        <td>
                                            <form action="{{ route('alertas.destroy', $item->id) }}" method="post" id="form-{{$item->id}}">
                                                @method('delete')
                                                @csrf
                                                <a href="{{ route('alertas.edit', $item) }}" class="btn btn-warning btn-sm text-white">
                                                    <i class="bx bx-edit"></i>
                                                </a>
                                                <button type="button" class="btn btn-delete btn-sm btn-danger">
                                                    <i class="bx bx-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="7" class="text-center">Nada encontrado</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
