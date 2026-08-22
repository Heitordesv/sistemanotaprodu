<tr class="tr_{{ $rand }}">
	<td>
		<input readonly type="tel" name="produto_id[]" class="form-control" value="{{$product->id}}">
	</td>
	<td>
		<input readonly type="text" name="produto_nome[]" class="form-control" value="{{$product->nome}}">
	</td>
	<td>
		<input readonly type="tel" name="valor_unitario[]" class="form-control value_unit_row" value="{{ __moeda($value_unit) }}">
	</td>
	<td>
		<input readonly type="tel" name="quantidade[]" class="form-control qtd-item qtd_row" value="{{ $qtd }}">
	</td>
	<td>
		<input type="hidden" name="x_pedido[]" id="x_pedido_row" class="x_pedido_row" value="">
		<input type="hidden" name="num_item_pedido[]" id="num_item_pedido_row" class="num_item_pedido_row" value="">
		<input readonly type="tel" name="subtotal_item[]" class="form-control subtotal-item" value="{{ __moeda($sub_total) }}">
	</td>

	
	<td>
		<button type="button" class="btn btn-sm btn-danger btn-delete-row">
			<i class="bx bx-trash"></i>
		</button>

		<button type="button" class="btn btn-sm btn-warning btn-edit" onclick="editItem('{{ $rand }}')">
			<i class="bx bx-edit"></i>
		</button>
	</td>
	<td>
		<input readonly type="hidden" name="cfop[]" class="" value="{{ $product->CFOP_saida_estadual }}">
	</td>
</tr>
