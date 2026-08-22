<tr class="line-product">
    <input readonly type="hidden" name="key" class="form-control" value="{{ $product->key }}">
    <input readonly type="hidden" name="produto_id[]" class="form-control" value="{{ $product->id }}">
 <input readonly type="hidden" name="is_os" class="form-control" value="{{ $idOs ?? null }}">
    <td>
        <input readonly type="text" name="produto_nome[]" class="form-control produto-nome" value="{{ $product->nome }}">
    </td>
    <td class="datatable-cell">
        <div class="form-group mb-2">
            <div class="input-group">
                <div class="input-group-prepend">
                    <button id="btn-subtrai" class="btn btn-danger" type="button">-</button>
                </div>
                <input type="tel" readonly class="form-control qtd" name="quantidade[]" value="{{ $qtd }}">
                <div class="input-group-append">
                    <button class="btn btn-success" id="btn-incrementa" type="button">+</button>
                </div>
            </div>
        </div>
    </td>

    <td>
        <input readonly type="tel" name="valor_unitario[]" class="form-control value-unit" value="{{ __moeda($value_unit) }}">
    </td>
    <td>
        <input readonly type="tel" name="subtotal_item[]" class="form-control subtotal-item" value="{{ __moeda($sub_total) }}">
    </td>
</tr>
