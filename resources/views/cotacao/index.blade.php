@extends('default.layout',['title' => 'Cotações'])
@section('content')
<div class="page-content">
    <div class="card ">
        <div class="card-body p-4">
            <div class="page-breadcrumb d-sm-flex align-items-center mb-3">
                <div class="">
                    <a href="{{ route('cotacao.create')}}" type="button" class="btn btn-success">
                        <i class="bx bx-plus"></i> Nova cotação
                    </a>
                    <a href="{{ route('cotacao.referencia')}}" type="button" class="btn btn-primary m-3">
                        <i class="bx bx-plus"></i>Cotações para referência
                    </a>
                </div>
            </div>
            <div class="col">
                <h6 class="mb-0 text-uppercase">Fornecedores</h6>
                {!!Form::open()->fill(request()->all())
                ->get()
                !!}
                <div class="row mt-2">
                    <div class="col-md-4">
                        {!!Form::select('fornecedor_id', 'Pesquisar por fornecedor')->attrs(['class' => 'select2'])
                        !!}
                    </div>
                    <div class="col-md-2">
                        {!!Form::date('start_date', 'Data inicial')
                        !!}
                    </div>
                    <div class="col-md-2">
                        {!!Form::date('end_date', 'Data final')
                        !!}
                    </div>
                    <div class="col-md-3 text-left ">
                        <br>
                        <button class="btn btn-primary" type="submit"> <i class="bx bx-search"></i>Pesquisa</button>
                        <a id="clear-filter" class="btn btn-danger" href="{{ route('cotacao.index') }}"><i class="bx bx-eraser"></i> Limpar</a>
                    </div>
                </div>
                {!!Form::close()!!}
                <hr />
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table mb-0 table-striped">
                                <thead>
                                    <tr>
                                        <th>Fornecedor</th>
                                        <th>Data criação</th>
                                        <th>Respondida</th>
                                        <th>Ativa</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($data as $item)
                                    <tr>
                                        <td>{{ $item->fornecedor->razao_social }}</td>
                                        <td>{{ __data_pt($item->created_at, 0) }}</td>
                                        <td>{{ $item->resposta == 0 ? 'Não' : 'Sim' }}</td>
                                        <td>{{ $item->ativa == 0 ? 'Não' : 'Sim' }}</td>
                                        <td>
                                            <form action="{{ route('cotacao.destroy', $item->id) }}" method="post" id="form-{{$item->id}}">
                                                @method('delete')
                                                @csrf
                                                <a href="{{ route('cotacao.show', $item) }}" type="btn" class="btn btn-info btn-sm" title="Ver">
                                                    <i class="bx bx-show"></i>
                                                </a>
                                                <button type="" class="btn btn-danger btn-sm" title="">
                                                    <i class="bx bx-trash"></i>
                                                </button>
                                                <a href="{{ route('cotacao.sendMail', $item->id) }}" class="btn btn-success btn-sm" title="Enviar e-mail">
                                                    <i class="bx bx-mail-send"></i>
                                                </a>
                                                <a href="/response/{{ $item->link }}" class="btn btn-warning btn-sm" title="Link">
                                                    <i class="bx bx-detail"></i>
                                                </a>

                                                @if($item->ativa == true)
                                                <a class="btn btn-danger btn-sm" href="/cotacao/alterarStatus/{{$item->id}}/0" title="Desativar">
                                                    <i class="bx bx-x"></i>
                                                </a>
                                                @else
                                                <a class="btn btn-info btn-sm" href="/cotacao/alterarStatus/{{$item->id}}/1" class="navi-link" title="Ativar">
                                                    <i class="bx bx-checkbox-checked"></i>
                                                </a>
                                                @endif
                                            </form>

                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="7" class="text-center">Nada encontrado</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        {!! $data->appends(request()->all())->links() !!}
    </div>
</div>
@endsection
