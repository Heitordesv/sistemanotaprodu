<tr>

	<td>
		<input readonly type="text" name="produto_nome[]" class="form-control" value="{{$item->nome}}">
	</td>
	<td>
		<input readonly type="tel" name="quantidade[]" class="form-control qtd-item" value="{{ $qtd }}">
	</td>
	<td>
		<button class="btn btn-sm btn-danger btn-delete-row">
			<i class="bx bx-trash"></i>
		</button>
	</td>
</tr>
