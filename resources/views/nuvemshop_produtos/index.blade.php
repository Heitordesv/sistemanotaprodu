@extends('default.layout',['title' => 'Produtos Nuvem Shop'])
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
                    <a href="{{ route('nuvemshop-produtos.create')}}" type="button" class="btn btn-success">
                        <i class="bx bx-plus"></i> Novo produto
                    </a>
                </div>
            </div>
            <div class="col">
                <h6 class="mb-0 text-uppercase">Produtos Nuvem Shop</h6>
                {!!Form::open()->fill(request()->all())
                ->get()
                !!}
                <div class="row">
                    <div class="col-md-3">
                        {!!Form::text('search', 'Descrição')
                        !!}
                    </div>

                    <div class="col-md-3 text-left ">
                        <br>
                        <button class="btn btn-primary" type="submit"> <i class="bx bx-search"></i>Pesquisar</button>
                        <a id="clear-filter" class="btn btn-danger" href="{{ route('nuvemshop-produtos.index') }}"><i class="bx bx-eraser"></i> Limpar</a>
                    </div>
                </div>
                {!!Form::close()!!}
                <hr />
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table mb-0 table-striped">
                                <thead class="">
                                    <tr>
                                        <th width=""></th>
                                        <th width="">Descrição</th>
                                        <th width="">NID</th>
                                        <th width="">Preço</th>
                                        <th width="">Preço promocional</th>
                                        <th width="">Estoque</th>
                                        <th width="">Código de barras</th>

                                        <th>Categoria(s)</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($produtos as $item)
                                    <tr>
                                        <td>
                                            @if(sizeof($item->images) > 0)
                                            <img class="img-round" src="{{$item->images[0]->src}}" alt="image">
                                            @else
                                            <img class="img-round" src="/imgs/no_image.png" alt="image">
                                            @endif
                                        </td>
                                        <td>{{ $item->name->pt }}</td>
                                        <td>{{ $item->id }}</td>
                                        <td>{{ __moeda($item->variants[0]->price) }}</td>
                                        <td>{{ __moeda($item->variants[0]->promotional_price) }}</td>
                                        <td>
                                            @if($item->variants[0]->stock == 0)
                                            ilimitado
                                            @else
                                            {{ __moeda($item->variants[0]->stock) }}
                                            @endif
                                        </td>
                                        <td>{{ $item->variants[0]->barcode }}</td>
                                        <td>{{ $item->name->pt }}</td>

                                        <td>
                                            @foreach($item->categories as $key => $c)
                                            {{$c->name->pt}} 

                                            @if($key < sizeof($item->categories)-1) | @endif
                                            @endforeach
                                        </td>
                                        <td>
                                            <form action="{{ route('nuvemshop-produtos.destroy', $item->id) }}" method="post" id="form-{{$item->id}}">
                                                @method('delete')
                                                <a href="{{ route('nuvemshop-produtos.edit', $item->id) }}" class="btn btn-warning btn-sm text-white">
                                                    <i class="bx bx-edit"></i>
                                                </a>

                                                <a href="{{ route('nuvemshop-produtos.galery', $item->id) }}" class="btn btn-info btn-sm text-white">
                                                    <i class="bx bx-photo-album"></i>
                                                </a>

                                                @csrf
                                               <!--  <button type="button" class="btn btn-delete btn-sm btn-danger">
                                                    <i class="bx bx-trash"></i>
                                                </button> -->
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
            @if(!isset($cliente))
            <div class="row">
                <div class="col-sm-1">
                    @if($page > 1)
                    <a class="btn btn-light-primary" href="/nuvemshop-produtos?page={{$page-1}}" class="float-left">
                        <i class="la la-angle-double-left"></i>
                    </a>
                    @endif
                </div>
                <div class="col-sm-10"></div>
                <div class="col-sm-1">
                    <a class="btn btn-light-primary" href="/nuvemshop-produtos?page={{$page+1}}" class="float-right">
                        <i class="la la-angle-double-right"></i>
                    </a>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
