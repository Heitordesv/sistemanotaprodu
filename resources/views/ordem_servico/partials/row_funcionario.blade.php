<tr>
	<td>
		<input readonly type="text" name="nome[]" class="form-control"
        value="{{ $funcionario->nome }}">
	</td>
	<td>
		<input readonly type="tel" name="funcao[]" class="form-control" value="{{ $funcao }}">
	</td>
	<td>
		<input readonly type="tel" name="celular[]" class="form-control" value="{{ $celular }}">
	</td>
	<td>
		<button class="btn btn-sm btn-danger btn-delete-row">
			<i class="bx bx-trash"></i>
		</button>
	</td>
</tr>
