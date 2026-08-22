@extends('default.layout',['title' => 'Categorias Nuvem Shop'])
@section('css')
<style type="text/css">
    .img-round{
        height: 80px !important;
        width: 80px !important;
        border: 1px solid #999;
    }
</style>
@endsection
@section('content')
<div class="page-content">
    <div class="card ">
        <div class="card-body p-4">
            <div class="page-breadcrumb d-sm-flex align-items-center mb-3">
                <div class="ms-auto">
                    <a href="{{ route('nuvemshop-categoria.create')}}" type="button" class="btn btn-success">
                        <i class="bx bx-plus"></i> Novo categoria
                    </a>
                </div>
            </div>
            <div class="col">
                <h6 class="mb-0 text-uppercase">Categorias Nuvem Shop</h6>
                
                <hr />
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table mb-0 table-striped">
                                <thead class="">
                                    <tr>
                                        <th width="">Descrição</th>
                                        <th width="">Subcategoria de</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php

                                    if(sizeof($categorias) > 0)
                                    $categoria = $categorias[0]->name->pt;
                                    @endphp
                                    @forelse($categorias as $item)
                                    <tr>

                                        <td>{{ $item->name->pt }}</td>
                                        <td>
                                            @if($item->parent > 0)
                                            {{ $categoria }}
                                            @else
                                            --
                                            @endif

                                        </td>
                                        <td>
                                            <form action="{{ route('nuvemshop-categoria.destroy', $item->id) }}" method="post" id="form-{{$item->id}}">
                                                @method('delete')
                                                <a href="{{ route('nuvemshop-categoria.edit', $item->id) }}" class="btn btn-warning btn-sm text-white">
                                                    <i class="bx bx-edit"></i>
                                                </a>


                                                @csrf
                                                <button type="button" class="btn btn-delete btn-sm btn-danger">
                                                    <i class="bx bx-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>

                                    @php
                                    if(!$item->parent)
                                    $categoria = $item->name->pt;
                                    @endphp
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
</div>
@endsection
