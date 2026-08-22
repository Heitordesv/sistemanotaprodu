<tr>
	<td>
		<input readonly type="date" name="vencimento_parcela[]" class="form-control" value="{{ $vencimento }}">
	</td>
	<td>
		<input readonly type="text" name="valor_parcela[]" class="form-control valor-parcela" value="{{ $valor_parcela }}">
	</td>
	
	<td>
		<button class="btn btn-sm btn-danger btn-delete-row">
			<i class="bx bx-trash"></i>
		</button>
	</td>
</tr>