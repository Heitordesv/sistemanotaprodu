@extends('default.layout', ['title' => 'Arquivos de remessa'])
@section('content')
<div class="page-content">
    <div class="card ">
        <div class="card-body p-4">
            <div class="page-breadcrumb d-sm-flex align-items-center mb-3">
                <div class="ms-auto">
                    <a href="{{ route('remessa.sem-remessa') }}" type="button" class="btn btn-info">
                        <i class="bx bx-list-ul"></i> Boletos sem remessa
                    </a>
                </div>
            </div>
            <div class="col">
                <h6 class="mb-0 text-uppercase">Remessas</h6>
                <hr />
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table mb-0 table-striped">
                                <thead class="">
                                    <tr>
                                        <th width="75%">Arquivo</th>
                                        <th>Data</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($data as $item)
                                    <tr>
                                        <td>{{ $item->nome_arquivo }}</td>
                                        <td>{{ __data_pt($item->created_at) }}</td>
                                        <td>
                                            <form action="{{ route('remessa-boletos.destroy', $item->id) }}" method="post" id="form-{{ $item->id }}">
                                                @method('delete')
                                                <a href="{{ route('remessa-boletos.show', $item) }}" class="btn btn-warning btn-sm text-white">
                                                    <i class="bx bx-file"></i>
                                                </a>
                                                @csrf
                                                <button type="button" class="btn btn-delete btn-sm btn-danger">
                                                    <i class="bx bx-trash"></i>
                                                </button>

                                                <a href="{{ route('remessa-boletos.download', $item) }}" class="btn btn-dark btn-sm text-white">
                                                    <i class="bx bx-download"></i>
                                                </a>
                                            </form>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="3" class="text-center">Nada encontrado</td>
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
