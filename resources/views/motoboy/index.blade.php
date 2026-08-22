@extends('default.layout',['title' => ' Motoboys'])
@section('content')
<div class="page-content">
    <div class="card ">
        <div class="card-body p-4">
            <div class="page-breadcrumb d-sm-flex align-items-center mb-3">
                <div class="ms-auto">
                    <a href="{{ route('motoboy.create')}}" type="button" class="btn btn-success">
                        <i class="bx bx-plus"></i>  Motoboys 
                    </a>
                </div>
            </div>
            <hr>
            <div class="col">
                <h6 class="mb-0 text-uppercase mt-4"> Motoboys</h6>
                <div class="card mt-2">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table mb-0 table-striped">
                                <thead class="">
                                    <tr>
                                        <th>user_id</th>
                                        <th>Nome</th>
                                        <th>Telefone</th>
                                       
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($motoboys as $item)
                                    <tr>
                                        <td>{{ $item->deliveryman_name }}</td>
                                        <td>{{ $item->deliveryman_phone_number }}</td>
                                        
                                        <td>{{ $item->Bearer }}</td>
                            
                                        <td>
                                            <form action="{{ route('motoboy.destroy', $item->id) }}" method="post" id="form-{{ $item->id }}">
                                                @method('delete')
                                              <a href="{{ route('motoboy.edit', $item) }}" class="btn btn-warning btn-sm text-white">
                                                    <i class="bx bx-edit"></i>
                                                </a>

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
