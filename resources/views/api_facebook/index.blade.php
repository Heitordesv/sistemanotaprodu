@extends('default.layout',['title' => ' API de Conversão do Facebook'])
@section('content')
<div class="page-content">
    <div class="card ">
        <div class="card-body p-4">
            <div class="page-breadcrumb d-sm-flex align-items-center mb-3">
                <div class="ms-auto">
                    <a href="{{ route('apiFacebook.create')}}" type="button" class="btn btn-success">
                        <i class="bx bx-plus"></i>  API de Conversão do Facebook
                    </a>
                </div>
            </div>
            <hr>
            <div class="col">
                <h6 class="mb-0 text-uppercase mt-4"> API de Conversão do Facebook</h6>
                <div class="card mt-2">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table mb-0 table-striped">
                                <thead class="">
                                    <tr>
                                        <th>nome_empresa</th>
                                        <th>pixel_id</th>
                                        <th>access_token</th>
                                       
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($data as $item)
                                    <tr>
                                        <td>{{ $item->nome_empresa }}</td>
                                        <td>{{ $item->pixel_id }}</td>
                                        
                                        <td>{{ $item->access_token }}</td>
                            
                                        <td>
                                            <form action="{{ route('apiFacebook.destroy', $item->id) }}" method="post" id="form-{{ $item->id }}">
                                                @method('delete')
                                              <!--  <a href="{{ route('apiFacebook.edit', $item) }}" class="btn btn-warning btn-sm text-white">
                                                    <i class="bx bx-edit"></i>
                                                </a>
                                            -->
                                                @csrf
                                                <button type="button" class="btn btn-delete btn-sm btn-danger">
                                                    <i class="bx bx-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                    @endforeach
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
