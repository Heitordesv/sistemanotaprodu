@foreach($item->eventosAtivos as $ev)

<tr class="datatable-row dynamic-form">
	<td class="datatable-cell">
		<span class="codigo" style="width: 70px;" id="id">
			<button type="button" class="btn btn-sm btn-danger btn-remove">
				<i class="bx bx-trash"></i>
			</button>
		</span>
	</td>

	<td class="datatable-cell">
		<span class="codigo" style="width: 200px;">
			<select required name="evento[]" class="form-select evento select-disabled">
				<option value="{{$ev->evento_id}}" data-condicao="{{ $ev->condicao }}"
					data-metodo="{{ $ev->metodo }}">{{$ev->evento->nome}}
				</option>
			</select>
		</span>
	</td>

	<td class="datatable-cell">
		<span class="codigo" style="width: 100px;" id="id">
			<select required name="condicao[]" class="form-select condicao_chave select-disabled" readonly>
				<option value="">Selecione</option>
				<option @if($ev->condicao == "soma") selected @endif value="soma">Soma</option>
				<option @if($ev->condicao == "diminui") selected @endif value="diminui">Diminui</option>
			</select>
		</span>
	</td>

	<td class="datatable-cell">
		<span class="codigo" style="width: 100px;">
			@if($ev->evento->tipo_valor == 'percentual')
			<input value="{{ number_format($item->salario * ($ev->valor/100), 2, ',', '') }}" required type="tel" name="valor[]" class="form-control value">
			@else
			<input value="{{ number_format($ev->valor, 2, ',', '') }}" required type="tel" name="valor[]" class="form-control value">
			@endif
		</span>
	</td>

	<td class="datatable-cell">
		<span class="codigo" style="width: 100px;" id="id">
			<select required name="metodo[]" class="form-select metodo select-disabled">
				<option value="">Selecione</option>
				<option @if($ev->metodo == "informado") selected @endif value="informado">Informado</option>
				<option @if($ev->metodo == "fixo") selected @endif value="fixo">Fixo</option>
			</select>
		</span>
	</td>

</tr>
@endforeach