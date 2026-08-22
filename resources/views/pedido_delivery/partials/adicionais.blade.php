
@foreach($data as $c)
<button type="button" class="btn btn-light adicionais_{{ $c->id }} adicionais" onclick="adicionais('{{$c->id}}', '{{$c->valor}}')" value="{{$c->id}}">
    {{$c->nome}} - {{$c->valor}}
</button>
@endforeach