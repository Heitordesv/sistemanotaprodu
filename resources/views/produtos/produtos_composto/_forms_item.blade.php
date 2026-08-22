<div class="row">

    <div class="col-lg-12">
        {!! Form::select('produto_id', 'Produto')->required() !!}
    </div>
    <div class="col-lg-4">
        {!! Form::tel('quantidade', 'Quantidade')->required() !!}
    </div>
    <div class="col-lg-6">
        {!! Form::select('medida', 'Unidade', App\Models\Produto::unidadesMedida())->attrs(['class' => 'select2']) !!}
    </div>
    <div class="col-lg-5">
        <br>
        <button type="submit" class="btn btn-primary btn-add-item">
            <i class="bx bx-plus"></i>Adicionar</button>
    </div>
</div>
<div class="row">
    <div class="table-reponsive">
        <table class="table mb-0 table-striped table-itens mt-2">
            <thead>
                <tr>
                    <th>Produto</th>
                    <th>Quantidade</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($item->itens as $i)
                    <tr>
                        <td>{{ $i->produto->nome }}</td>
                        <td>{{ $i->quantidade }}</td>
                        <td>{{ $i->quantidade }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<div class="col-12 mt-5">
    <hr>
    <a href="{{ route('produtos.index') }}" class="btn btn-primary">Salvar</a>
</div>
