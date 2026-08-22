@extends('default.layout',['title' => 'Tela pedido'])
@section('content')
<div class="page-content">
    <div class="card ">
        <div class="card-body p-4">
            <div class="page-breadcrumb d-sm-flex align-items-center mb-3">
                <div class="ms-auto">
                    <a href="{{ route('telasPedido.create')}}" type="button" class="btn btn-success">
                        <i class="bx bx-plus"></i> Nova tela de pedido
                    </a>
                </div>
            </div>
            <hr>
            <div class="col">
                <h6 class="mb-0 text-uppercase">Telas de pedido</h6>
                <div class="card mt-3">
                    <div class="row mt-3">
                        @foreach($item as $t)
                        <div class="col-sm-12 col-lg-6 col-md-6 col-xl-4">
                            <div class="card border-top border-0 border-4 border-primary m-3">
                                <div class="card-body" style="position: relative;">
                                    <div class="d-flex align-items-center">
                                        <div class="">
                                            <h5 class="card-title">{{$t->nome}}</h5>
                                        </div>
                                        <div class="ms-auto">
                                            <form action="{{ route('telasPedido.destroy', $t->id) }}" method="post" id="form-{{$t->id}}">
                                                @method('delete')
                                                <a href="{{ route('telasPedido.edit', $t->id) }}" class="btn btn-warning text-white btn-sm"><i class="bx bx-edit"></i></a>
                                                @csrf
                                                <button type="button" class="btn btn-delete btn-sm btn-danger">
                                                    <i class="bx bx-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
