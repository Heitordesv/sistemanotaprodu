<div class="row">
    @foreach ($comb as $c)
    <tr>
        <td>
            <input readonly type="text" name="tamanho_grade[]" class="form-control" value="{{$c['nome']}}">
        </td>
        <td>
            <input  type="text" name="codigo_barras_grade[]" class="form-control" value="">
        </td>
        <td>
            <input  type="tel" name="quantidade_grade[]" class="form-control moeda quantidade_grade" value="">
        </td>
        <td>
            <input  type="tel" name="valor_grade[]" class="form-control moeda valor_grade" value="">
        </td>
    </tr>
    @endforeach
</div>
