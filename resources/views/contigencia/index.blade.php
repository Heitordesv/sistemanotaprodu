@extends('default.layout',['title' => 'Contigência'])
@section('content')
<div class="page-content">
    <div class="card ">
        <div class="card-body p-4">
            <div class="page-breadcrumb d-sm-flex align-items-center mb-3">
                <div class="ms-auto">
                    <a href="{{ route('contigencia.create')}}" type="button" class="btn btn-success">
                        <i class="bx bx-plus"></i> Ativar contigência
                    </a>
                </div>
            </div>
            <div class="col">

                <br>
                <hr>
                <h5 class="mt-3">Lista de contigência</h5>
                <p style="color: mediumblue">Registros: {{ sizeof($data) }}</p>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table mb-0 table-striped">
                            <thead>
                                <tr>
                                    <th>Data</th>
                                    <th>Motivo</th>
                                    <th>Tipo</th>
                                    <th>Documento</th>
                                    <th>Status</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($data as $item)
                                <tr>
                                    <td>{{ __data_pt($item->created_at) }}</td>
                                    <td>{{ $item->motivo }}</td>
                                    <td>{{ $item->tipo }}</td>
                                    <td>{{ $item->documento }}</td>
                                    <td class="td-icon">
                                        @if($item->status)
                                        <i class="bx bx-check text-success"></i>
                                        @else
                                        <i class="bx bx-x-circle text-danger"></i>
                                        @endif
                                    </td>
                                    <td>
                                        @if($item->status)
                                        <a href="{{ route('contigencia.desactive', [$item->id]) }}" class="btn btn-danger btn-sm">Desativar</a>
                                        @endif
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
        </div>
    </div>
</div>
@endsection
