<div class="row g-3">
    <div class="col-md-6">
        {!! Form::text('nome', 'Nome')->required() !!}
    </div>
    <hr>
    <h5>Permissão de Acesso:</h5>
    <input type="hidden" id="menus" value="{{ json_encode($menu) }}" name="">
    @foreach ($menu as $m)
    <div class="col-12 col-form-label">
        <span>
            <label class="checkbox checkbox-info">
                <input id="todos_{{ str_replace(' ', '_', $m['titulo']) }}" onclick="marcarTudo('{{ $m['titulo'] }}')" type="checkbox">
                <span></span><strong class="text-info" style="margin-left: 5px; font-size: 16px;">{{ $m['titulo'] }} </strong>
            </label>
        </span>
        <div class="checkbox-inline" style="margin-top: 10px;">
            @foreach ($m['subs'] as $s)
            @if ($s['nome'] != 'NFS-e')
            @php
            $link = str_replace('/', '', $s['rota']);
            $link = str_replace('.', '_', $link);
            $link = str_replace(':', '_', $link);
            @endphp
            {{-- <label class="checkbox checkbox-info check-sub">
                            <input id="sub_{{ str_replace('/', '', $s['rota']) }}"
            @if (in_array($s['rota'], $permissoesAtivas)) checked @endif type="checkbox"
            name="{{ $s['rota'] }}">
            <span></span>{{ $s['nome'] }} --}}
            </label>
            <label class="checkbox checkbox-info check-sub">
                <input id="sub_{{ $link }}" @if (\App\Models\Empresa::validaLink($s['rota'], $permissoesAtivas)) checked @endif type="checkbox" name="{{ $s['rota'] }}">
                <span></span>{{ $s['nome'] }}
            </label>
            @endif
            @endforeach
        </div>
    </div>
    @endforeach
    <div class="col-12">
        <button type="submit" class="btn btn-primary px-5">Salvar</button>
    </div>
</div>

@section('js')
<script type="" src="/js/perfil.js"></script>
@endsection
