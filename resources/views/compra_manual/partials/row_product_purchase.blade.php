<tr>
	<td>
		<input readonly type="tel" name="produto_id[]" class="form-control" value="{{$product->id}}">
	</td>
	<td>
		<input readonly type="text" name="produto_nome[]" class="form-control" value="{{$product->nome}}">
	</td>
	<td>
		<input readonly type="tel" name="valor_unitario[]" class="form-control" value="{{ __moeda($value_unit) }}">
	</td>
	<td>
		<input readonly type="tel" name="quantidade[]" class="form-control qtd-item" value="{{ $qtd }}">
	</td>
	<td>
		<input readonly type="tel" name="subtotal_item[]" class="form-control subtotal-item" value="{{ __moeda($sub_total) }}">
	</td>
	<td>
		<button class="btn btn-sm btn-danger btn-delete-row">
			<i class="bx bx-trash"></i>
		</button>
	</td>
</tr>