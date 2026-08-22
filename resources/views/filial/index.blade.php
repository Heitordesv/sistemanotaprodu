@extends('default.layout',['title' => 'Filiais'])
@section('content')
<div class="page-content">
    <div class="card ">
        <div class="card-body p-4">
            <div class="page-breadcrumb d-sm-flex align-items-center mb-3">
                <div class="ms-auto">
                    <a href="{{ route('filial.create')}}" type="button" class="btn btn-success">
                        <i class="bx bx-plus"></i> Nova Localização
                    </a>
                </div>
            </div>
            <div class="col">
                <hr>
                <h4>Filiais</h4>
                <div class="mt-3">
                    <div class="table-responsive">
                        <table class="table mb-0 table-striped">
                            <thead class="">
                                <tr>
                                    <th>Descrição</th>
                                    <th>Razão social</th>
                                    <th>Documento</th>
                                    <th>Data cadastro</th>
                                    <th>Ativo</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($data as $item)
                                <tr>
                                    <td>{{ $item->descricao }}</td>
                                    <td>{{ $item->razao_social }}</td>
                                    <td>{{ $item->cnpj }}</td>
                                    <td>{{ $item->created_at }}</td>
                                    <td>
                                        @if($item->status)
                                        <span class="btn btn-success btn-sm">Sim</span>
                                        @else
                                        <span class="btn btn-danger btn-sm">Não</span>
                                        @endif
                                    </td>
                                    <td>
                                        <form action="{{ route('filial.destroy', $item->id) }}" method="post" id="form-{{$item->id}}">
                                            @method('delete')
                                            <a href="{{ route('filial.edit', $item) }}" class="btn btn-warning btn-sm text-white">
                                                <i class="bx bx-edit"></i>
                                            </a>
                                            @csrf
                                            <button type="button" class="btn btn-delete btn-sm btn-danger">
                                                <i class="bx bx-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
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
@endsection
