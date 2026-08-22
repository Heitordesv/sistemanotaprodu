@extends('default.layout',['title' => 'Custos CTe'])
@section('content')
<div class="page-content">
    <div class="card ">
        <div class="card-body">
            <div class="page-breadcrumb d-sm-flex align-items-center mt-3">
                <div class="ms-auto">
                    <a href="{{ route('cte.index')}}" type="button" class="btn btn-light btn-sm">
                        <i class="bx bx-arrow-back"></i> Voltar
                    </a>
                </div>
            </div>
            <hr>
            <div class="page-breadcrumb d-sm-flex">
                <div class="col m-3">
                    <h6 class="mb-0 text-uppercase">DESPESAS</h6>
                    {!!Form::open()
                    ->post()
                    ->route('cte.storeDespesa', [$item->id])
                    !!}
                    <input type="hidden" name="cte_id" value="{{$item->id}}">
                    <div class="row mt-4">
                        <div class="col-md-12">
                            {!!Form::text('descricao', 'Descrição')
                            !!}
                        </div>
                        <div class="col-6 mt-3">
                            {!!Form::select('categoria_id', 'Categoria', ['' => 'Selecione'] + $categoria->pluck('nome', 'id')->all())->attrs(['class' => 'form-select'])
                            !!}
                        </div>
                        <div class="col-6 mt-3">
                            {!!Form::tel('valor', 'Valor')->attrs(['class' => 'moeda'])
                            !!}
                        </div>
                        <div class="mt-4">
                            <button style="" type="submit" class="btn btn-danger px-5">Salvar</button>
                        </div>
                    </div>
                    {!!Form::close()!!}
                    <hr>
                    <div class="table-responsive mt-4">
                        <table class="table mb-0 table-striped">
                            <thead>
                                <tr>
                                    <th>Descrição</th>
                                    <th>Categoria</th>
                                    <th>Valor</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($item->despesas as $d)
                                <tr>
                                    <td>{{ $d->descricao }}</td>
                                    <td>{{ $d->categoria->nome }}</td>
                                    <td>{{ __moeda($d->valor) }}</td>
                                    <td>
                                        <form action="{{ route('cte.deleteDespesa', $d->id) }}" method="" id="{{$d->id}}">
                                            @method('delete')
                                            @csrf
                                            <button type="submit" class="btn btn-delete btn-sm btn-danger">
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
                        <div class="mt-2">
                            <h5 style="color: crimson">Total: {{ __moeda($item->somaDespesa()) }}</h5>
                        </div>
                    </div>
                </div>

                <div class="col mt-3">
                    <h6 class="mb-0 text-uppercase">RECEITAS</h6>
                    {!!Form::open()
                    ->post()
                    ->route('cte.storeReceita', [$item->id])
                    !!}
                    <input type="hidden" name="cte_id" value="{{$item->id}}">
                    <div class="row mt-4">
                        <div class="col-md-12">
                            {!!Form::text('descricao', 'Descrição')
                            !!}
                        </div>
                        <div class="col-md-6 mt-3">
                            {!!Form::tel('valor', 'Valor')->attrs(['class' => 'moeda'])
                            !!}
                        </div>
                        <div class="mt-4">
                            <button style="" type="submit" class="btn btn-info px-5">Salvar</button>
                        </div>
                    </div>
                    {!!Form::close()!!}
                    <hr>
                    <div class="table-responsive mt-4">
                        <table class="table mb-0 table-striped">
                            <thead>
                                <tr>
                                    <th>Descrição</th>
                                    <th>Valor</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($item->receitas as $r)
                                <tr>
                                    <td>{{ $r->descricao }}</td>
                                    <td>{{ __moeda($r->valor) }}</td>
                                    <td>
                                        <form action="{{ route('cte.deleteReceita', $r->id) }}" method="" id="{{$r->id}}">
                                            @method('delete')
                                            @csrf
                                            <button type="submit" class="btn btn-delete btn-sm btn-danger">
                                                <i class="bx bx-trash"></i>
                                            </button>
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
                        <div class="mt-2">
                            <h5 style="color: cornflowerblue">Total: {{ __moeda($item->somaReceita()) }}</h5>
                        </div>
                    </div>
                </div>
            </div>
            <div class="m-3">
                <h4>Saldo: <strong class="@if($item->somaReceita()>$item->somaDespesa()) text-success
                    @elseif($item->somaReceita()==$item->somaDespesa()) text-primary
                    @else text-danger @endif"> {{ __moeda($item->somaReceita() - $item->somaDespesa()) }}</strong>
                </h4>
            </div>
        </div>
    </div>
</div>
@endsection
