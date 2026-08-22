<div class="row g-3">
    <div class="row mt-4">
        <div class="col-md-8">
            <label for="inp-fornecedor_id" class="">Fornecedor</label>
            <div class="input-group">
                <select class="form-control select2 fornecedor_id" name="fornecedor_id" id="inp-fornecedor_id">
                </select>
                <button class="btn btn-primary" type="button" data-bs-toggle="modal" data-bs-target="#modal-fornecedor">
                    <i class="bx bx-plus"></i></button>
            </div>
        </div>
    </div>
    <div class="row mt-4">
        <div class="row">
            <h6 class="mt-4">Itens Cotação</h6>
            <div class="col-md-6 mt-2">
                {!! Form::select('produto_id', 'Produto')->required()->attrs(['class' => 'ignore']) !!}
            </div>
            <div class="col-md-2 mt-2">
                {!! Form::tel('quantidade', 'Quantidade')->required()->attrs(['class' => 'ignore']) !!}
            </div>
            <div class="col-md-3 mt-2">
                <br>
                <button type="button" class="btn btn-info px-5 btn-add-item" id="">Adicionar</button>
            </div>
        </div>
        <div class="row mt-3">
            <div class="table-responsive">
                <table class="table mb-0 table-striped table-itens">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Produto</th>
                            <th>Quantidade</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @isset($item)
                        @forelse($item->itens as $product)
                        <tr>
                            <td>
                                <input readonly type="tel" name="produto_id[]" class="form-control" value="{{$product->produto_id}}">
                            </td>
                            <td>
                                <input readonly type="text" name="produto_nome[]" class="form-control" value="{{$product->produto->nome}}">
                            </td>
                            <td>
                                <input readonly type="tel" name="quantidade[]" class="form-control qtd-item" value="{{ $product->quantidade }}">
                            </td>
                            <td>
                                <button class="btn btn-sm btn-danger btn-delete-row">
                                    <i class="bx bx-trash"></i>
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center">Nada encontrado</td>
                        </tr>
                        @endforelse
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="row mt-3">
        <div class="col-md-4">
            {!! Form::text('referencia', 'Referência (necessário para clonar)')->attrs(['class' => 'ignore']) !!}
        </div>
        <div class="col-md-8">
            {!! Form::text('obs', 'Observação')->attrs(['class' => 'ignore']) !!}
        </div>
    </div>

    <div class="col-12" style="text-align: right;">
        @isset($not_submit)
        <button type="button" class="btn btn-primary px-5" id="">Salvar</button>
        @else
        <button type="submit" class="btn btn-primary px-5">Salvar</button>
        @endif
    </div>
</div>
