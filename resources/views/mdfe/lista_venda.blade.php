@foreach($data as $item)
<tr>
    <td>
        <input type="checkbox" class="checkbox" value="{{$item->id}}" name="">
    </td>
    <td>{{ __data_pt($item->created_at, 1) }}</td>
    <td>{{ $item->cliente->razao_social }}</td>
    <td>{{ __moeda($item->valor_total) }}</td>
    <td>{{ $item->chave }}</td>
    <td>{{ $item->numero_nfe }}</td>
</tr>
@endforeach
