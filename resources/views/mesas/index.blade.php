@extends('default.layout',['title' => 'Mesas'])
@section('content')
<div class="page-content">
    <div class="card ">
        <div class="card-body p-4">
            <div class="page-breadcrumb d-sm-flex align-items-center mb-3">
                <div class="ms-auto">
                    <a href="{{ route('mesas.create')}}" type="button" class="btn btn-success">
                        <i class="bx bx-plus"></i> Nova mesa
                    </a>
                    
                </div>
            </div>
            <div class="row">
                <hr>
                <h4>Lista de mesas</h4>
                @foreach($data as $item)
                <div class="col-4">
                    <div class="card radius-10">
                        <div class="card-body ">
                            <div class="d-flex align-items-center">
                                <div class="">
                                    <h6>
                                        {{ $item->nome }}
                                    </h6>
                                </div>
                                <div class="dropdown ms-auto">
                                    <div class="dropdown-toggle dropdown-toggle-nocaret cursor-pointer" data-bs-toggle="dropdown"> <i class='bx bx-dots-horizontal-rounded font-22'></i>
                                    </div>
                                    <ul class="dropdown-menu">
                                        <li><a class="dropdown-item" href="{{ route('mesas.edit', $item->id) }}">Editar</a>
                                        </li>
                                        <li>
                                            <a onclick='swal("Atenção!", "Deseja remover este registro?", "warning").then((sim) => {if(sim){ location.href="/mesas/delete/{{ $item->id }}" }else{return false} })' href="#!" class="dropdown-item">
                                                Excluir
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="m-1">
                            <!-- <span class="m-3">Token mesa: <strong>{{$item->token}}</strong></span> -->
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endsection
