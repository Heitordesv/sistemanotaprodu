@extends('default.layout',['title' => 'Controle comandas'])
@section('content')

<div class="page-content">
    <div class="card border-top border-0 border-3 border-success">
        <div class="card-body p-4">
            {!!Form::open()->fill(request()->all())
            ->get()
            !!}
            <div class="row mt-3">
                <div class="col-md-3">
                    {!!Form::text('comanda', 'Comanda')
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
                <div class="col-md-4 text-left">
                    <br>
                    <button class="btn btn-primary" type="submit"> <i class="bx bx-search"></i>Pesquisar</button>
                    <a id="clear-filter" class="btn btn-danger" href="{{ route('orcamentoVenda.index') }}"><i class="bx bx-eraser"></i> Limpar</a>
                </div>
            </div>
            {!!Form::close()!!}
            <br>
            <h4 class="mt-4">Controle de Comandas</h4>
            <label>Número de registros: {{count($comandas)}}</label>
            @isset($mensagem)
            <p class="text-danger mt-2">{{$mensagem}}</p>
            @endif
            <p class="text-danger mt-2">*Comanda em vermelho contém produtos deletados</p>
            <div class="col-sm-12 col-lg-12 col-md-12 col-xl-12">
                <div class="row">
                    <div class="col-xl-12">
                        <div class="table-responsive">
                            <table class="table mb-0 table-striped">
                                <thead class="datatable-head">
                                    <tr class="datatable-row" style="left: 0px;">
                                        <th data-field="OrderID" class="datatable-cell datatable-cell-sort"><span style="width: 70px;">#</span></th>
                                        <th data-field="OrderID" class="datatable-cell datatable-cell-sort"><span style="width: 100px;">Comanda</span></th>
                                        <th data-field="OrderID" class="datatable-cell datatable-cell-sort"><span style="width: 100px;">Observação</span></th>
                                        <th data-field="OrderID" class="datatable-cell datatable-cell-sort"><span style="width: 100px;">Valor</span></th>
                                        <th data-field="OrderID" class="datatable-cell datatable-cell-sort"><span style="width: 100px;">Data de Criação</span></th>
                                        <th data-field="OrderID" class="datatable-cell datatable-cell-sort"><span style="width: 100px;">Data de Finalização</span></th>
                                        <th data-field="OrderID" class="datatable-cell datatable-cell-sort"><span style="width: 100px;">Ações</span></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($comandas as $v)
                                    <tr @if($v->temItemDeletetado()) bg-danger @endif>
                                        <td>{{$v->id}}</td>
                                        <td>{{$v->comanda}}</td>
                                        <td><a href="#!" onclick='swal("", "{{$v->observacao}}", "info")' class="btn btn-info @if(!$v->observacao) disabled @endif">
                                                Ver
                                            </a>
                                        </td>
                                        <td>{{ __moeda($v->somaItems()) }}</td>
                                        <td>{{ \Carbon\Carbon::parse($v->created_at)->format('d/m/Y H:i:s')}}</td>
                                        <td>{{ \Carbon\Carbon::parse($v->updated_at)->format('d/m/Y H:i:s')}}</td>
                                        <td><a target="_blank" href="{{ route('pedidos.verDetalhes', $v->id) }}" class="btn btn-info btn-sm">
                                                <i class="bx bx-list-ul"></i>
                                            </a>
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
</div>


@endsection
