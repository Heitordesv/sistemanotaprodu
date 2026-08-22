@extends('default.layout', ['title' => 'Divisão de Grade'])
@section('content')
<div class="page-content">
    <div class="card ">
        <div class="card-body p-4">
            <div class="page-breadcrumb d-sm-flex align-items-center mb-3">
                <div class="ms-auto">
                    <a href="{{ route ('divisaoGrade.create')}}" type="button" class="btn btn-success">
                        <i class="bx bx-plus"></i>Nova divisão de grade</a>
                </div>
            </div>
            <hr>
            <div class="card">
                <h5 class="m-3">Lista da divisão de grade</h5>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table mb-0 table-striped">
                            <thead class="">
                                <tr>
                                    <th>Id</th>
                                    <th width="50%">Nome</th>
                                    <th width="40%">Info</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($data as $item)
                                <tr>
                                    <td>{{ $item->id }}</td>
                                    <td>{{ $item->nome }}</td>
                                    {{-- <td>{{ $item->sub_divisao == 0  ? 'Divisão' : 'Sub-divisão' }} --}}
                                    <td>
                                        @if ($item->sub_divisao == 0)
                                        <span class="btn btn-info btn-sm">Divisão</span>
                                        @else
                                        <span class="btn btn-warning btn-sm">Sub-divisão</span>
                                        @endif
                                    </td>
                                    </td>
                                    <td>
                                        <form action="{{ route('divisaoGrade.destroy', $item->id) }}" method="post" id="form-{{$item->id}}">
                                            @method('delete')
                                            <a href="{{ route('divisaoGrade.edit', $item) }}" class="btn btn-warning btn-sm text-white">
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
                                    <td colspan="4" class="text-center">Nada encontrado</td>
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
@endsection
