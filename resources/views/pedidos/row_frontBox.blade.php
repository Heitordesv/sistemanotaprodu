@foreach($itens as $i)
<tr class="line-product">
    <input readonly type="hidden" name="key" class="form-control" value="0">
    <input readonly type="hidden" name="produto_id[]" class="form-control" value="{{ $i->produto->id }}">

    <td>
        <input readonly type="text" name="produto_nome[]" class="form-control produto-nome" value="{{ $i->produto->nome }}">
    </td>
    <td class="datatable-cell">
        <div class="form-group mb-2">
            <div class="input-group">
                <div class="input-group-prepend">
                    <button id="btn-subtrai" class="btn btn-danger" type="button">-</button>
                </div>
                <input type="tel" readonly class="form-control qtd" name="quantidade[]" value="{{ $i->quantidade }}">
                <div class="input-group-append">
                    <button class="btn btn-success" id="btn-incrementa" type="button">+</button>
                </div>
            </div>
        </div>
    </td>

    <td>
        <input readonly type="tel" name="valor_unitario[]" class="form-control value-unit" value="{{ __moeda($i->valor) }}">
    </td>
    <td>
        <input readonly type="tel" name="subtotal_item[]" class="form-control subtotal-item" value="{{ __moeda($i->valor) }}">
    </td>
</tr>
@endforeach
