@foreach ($tamanhos as $item)
<div class="col-md-2">
    <td class="">
        <input readonly type="hidden" name="tamanho_id[]" class="form-control" value="{{ $item->id }}">
        <label>{{$item->nome}}</label>
        <input type="tel" name="valor_pizza[]" class="form-control moeda" value="">
    </td>
</div>
@endforeach
