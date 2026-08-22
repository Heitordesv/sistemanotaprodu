@extends('default.layout',['title' => 'Financeiro'])
@section('content')
<div class="page-content">
    <div class="card ">
        <div class="card-body p-4">
            <div class="col">
                {!!Form::open()->fill(request()->all())
                ->get()
                !!}
                <h5>Financeiro</h5>
                <div class="row">
                    <div class="col-md-4">
                        {!!Form::text('nome', 'Pesquisar empresa')
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
                    <div class="col-md-2">
                        {!!Form::select('estado', 'Estado', ['todos' => 'Todos', 'ativo' => 'Ativo', 'pendente' => 'Pendente', 'desativado' => 'Desativado'])->attrs(['class' => 'form-select'])
                        !!}
                    </div>
                    <div class="col-md-2">
                        {!!Form::select('tipo', 'Tipo de pagamento', ['todos' => 'Todos', 'cartao', 'Cartão', 'boleto' => 'Boleto', 'pix' => 'Pix'])->attrs(['class' => 'form-select'])
                        !!}
                    </div>
                    <div class="col-md-6 text-left ">
                        <br>
                        <button class="btn btn-primary" type="submit"><i class="bx bx-search"></i>Pesquisar</button>
                        <a id="clear-filter" class="btn btn-danger" href="{{ route('financeiro.index') }}"><i class="bx bx-eraser"></i> Limpar</a>
                    </div>
                </div>
                <hr>
                {!!Form::close()!!}
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <h5>Lista de Pagamentos</h5>
                            <p>Registros: </p>
                            <a class="btn btn-info" href="{{ route('financeiro.list') }}"><i class="bx bx-plus"></i> Pagamento manual</a>
                            <a class="btn btn-primary" href=""><i class="bx bx-recycle"></i> Verificar pagamento</a>
                            <table class="table mb-0 table-striped">
                                <thead class="">
                                    <tr>
                                        <th>#</th>
                                        <th>Empresa</th>
                                        <th>Data</th>
                                        <th>Plano</th>
                                        <th>Valor</th>
                                        <th>Status</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($data as $item)
                                    <tr>
                                        <td>{{$item}}</td>
                                    </tr>
                                    @endforeach

                                </tbody>
                            </table>
                        </div>
                    </div>
                    <h5 class="m-3">Soma: <strong style="color: blue">0,00</strong></h5>
                </div>
            </div>
        </div>
        {!! $data->appends(request()->all())->links() !!}
    </div>
</div>
@endsection
