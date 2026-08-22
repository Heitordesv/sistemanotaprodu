@extends('default.layout',['title' => 'Dre'])
@section('content')
<div class="page-content">
    <div class="card ">
        <div class="card-body p-4">
            <div class="page-breadcrumb d-sm-flex align-items-center mb-3">
                <div class="ms-auto">
                    <a href="{{ route('dre.index')}}" type="button" class="btn btn-light btn-sm">
						<i class="bx bx-arrow-back"></i> Voltar
					</a>
                </div>
            </div>
            <div class="col">
                <h6>DRE</h6>
            </div>
            <hr>
            <div class="table-responsive">
                <table class="table mb-0 table-striped">
                    <thead>
                        <tr>
                            <th>Data criação</th>
                            <th>Data inicial</th>
                            <th>Data final</th>
                            <th>Lucro/Prejuízo</th>
                            <th>Observação</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($data as $item)
                        <tr>
                            <td>{{ __data_pt($item->created_at) }}</td>
                            <td>{{ __data_pt($item->inicio, 0) }}</td>
                            <td>{{ __data_pt($item->fim, 0) }}</td>
                            <td>{{ __moeda($item->lucro_prejuizo) }}</td>
                            <td>{{ $item->observacao != "" ? $item->observacao : "--" }}</td>
                            <td>
                                <form action="{{ route('dre.destroy', $item->id) }}" method="post" id="form-{{$item->id}}">
                                    @method('delete')
                                    @csrf
                                    <button type="button" class="btn btn-delete btn-sm btn-danger">
                                        <i class="bx bx-trash"></i>
                                    </button>
                                    <a href="{{ route('dre.show', $item) }}" class="btn btn-info btn-sm text-white">
                                        <i class="bx bx-show"></i>
                                    </a>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center">Nada encontrado</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
