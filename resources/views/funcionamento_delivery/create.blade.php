@extends('default.layout',['title' => 'Funcionamento de Delivery'])
@section('content')
<div class="page-content">
    <div class="card border-top border-0 border-4 border-primary">
        <div class="card-body p-5">
            <div class="card-title d-flex align-items-center">
                <h5 class="mb-0 text-primary">Funcionamento de delivery</h5>
            </div>
            <hr>
            {!!Form::open()
            ->post()
            ->route('funcionamentoDelivery.store')
            ->multipart()!!}
            <div class="pl-lg-4">
                @include('funcionamento_delivery._forms')
            </div>
            {!!Form::close()!!}

            <div class="table-responsive mt-3">
                <table class="table mb-0 table-striped">
                    <thead>
                        <tr>
                            <th>Dia</th>
                            <th>Início</th>
                            <th>Fim</th>
                            <th>Status</th>
                            <th style="width: 150px">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($data as $item)
                        <tr>
                            <td>{{ $item->getDia($item->dia) }}</td>
                            <td>{{ $item->inicio_expediente }}</td>
                            <td>{{ $item->fim_expediente }}</td>
                            <td>
                                @if($item->ativo)
                                <button class="btn btn-info">Ativo</button>
                                @else
                                <button class="btn btn-warning">Desativado</button>
                                @endif
                            </td>
                            <td>
                                <form action="{{ route('funcionamentoDelivery.destroy', $item->id) }}" method="post" id="form-{{$item->id}}">
                                    @method('delete')
                                    @csrf
                                    <button title="Deletar" type="button" class="btn btn-sm btn-delete btn-danger">
                                        <i class="bx bx-trash"></i>
                                    </button>
                                    <a href="{{ route('funcionamentoDelivery.alterarStatus', $item->id) }}" title="Alterar Status" class="btn btn-info btn-sm"><i class="bx bx-refresh"></i></a>
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
