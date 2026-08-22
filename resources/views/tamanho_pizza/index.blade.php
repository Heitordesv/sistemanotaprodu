@extends('default.layout',['title' => 'Tamanho de Pizza'])
@section('content')
<div class="page-content">
    <div class="card ">
        <div class="card-body p-4">
            <div class="page-breadcrumb d-sm-flex align-items-center mb-3">
                <div class="ms-auto">
                    <a href="{{ route('tamanhosPizza.create')}}" type="button" class="btn btn-success">
                        <i class="bx bx-plus"></i> Novo tamanho de pizza
                    </a>
                </div>
            </div>
            <div class="col">
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <h6 class="mb-0 text-uppercase">Tamanho de pizza</h6>
                            <p class="mt-2">Registros: {{ sizeof($data) }}</p>
                            <table class="table mb-0 table-striped">
                                <thead class="">
                                    <tr>
                                        <th>Nome</th>
                                        <th>Pedaços</th>
                                        <th>Máximo de sabores</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($data as $item)
                                    <tr>
                                        <td>{{ $item->nome }}</td>
                                        <td>{{ $item->pedacos }}</td>
                                        <td>{{ $item->maximo_sabores }}</td>
                                        <td>
                                            <form action="{{ route('tamanhosPizza.destroy', $item->id) }}" method="post" id="form-{{$item->id}}">
                                                @method('delete')
                                                <a href="{{ route('tamanhosPizza.edit', $item) }}" class="btn btn-warning btn-sm text-white">
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
                                        <td colspan="7" class="text-center">Nada encontrado</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            {{-- {!! $data->appends(request()->all())->links() !!} --}}
        </div>
    </div>
</div>
@endsection
