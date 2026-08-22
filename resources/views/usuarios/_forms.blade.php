@section('css')
<style type="text/css">
    input[type='checkbox']:hover {
        cursor: pointer;
    }

</style>
@endsection
<div class="row g-3">
    <div class="col-md-3">
        {!! Form::text('nome', 'Nome')->required() !!}
    </div>

    <div class="col-md-2">
        {!! Form::text('login', 'Login')->required() !!}
    </div>
    <div class="col-md-2">
        {!! Form::text('senha', 'Senha')->type('password') !!}
    </div>

    <div class="col-md-3">
        {!! Form::text('email', 'Email')->type('email')->required() !!}
    </div>

    <div class="col-md-5">
        @php
        $appendAttr = '<label class="text-info label-popover" data-bs-container="body" data-bs-toggle="popover" data-bs-placement="bottom" data-bs-content="Copie o caminho completo da URL, exemplo: http://meusistema.com.br/frenteCaixa" data-bs-original-title="" title="">
            <i class="bx bx-info-circle"></i>
        </label>';

        @endphp
        {!! Form::text('rota_acesso', 'Rota padrão ao logar (opcional) ' . $appendAttr)->attrs(['class' => 'popover-button'])->wrapperAttrs(['']) !!}
    </div>

    <div class="col-md-2">
        {!! Form::select('ativo', 'Ativo', [1 => 'Sim', 0 => 'Não'])->attrs(['class' => 'form-select']) !!}
    </div>
    <div class="col-md-2">
        {!! Form::select('aviso_sonoro', 'Aviso sonoro', [1 => 'Sim', 0 => 'Não'])->attrs(['class' => 'form-select']) !!}
    </div>

    <div class="col-md-2">
        {!! Form::select('adm', 'ADM', [0 => 'Não', 1 => 'Sim'])->attrs(['class' => 'form-select']) !!}
    </div>

    <div class="col-md-2">
        {!! Form::select('somente_fiscal', 'Somente fiscal', [1 => 'Sim', 0 => 'Não'])->attrs(['class' => 'select2']) !!}
    </div>

    <div class="col-md-2 mt-2">
        @php
        $appendAttr = '<label class="text-info label-popover" data-bs-container="body" data-bs-toggle="popover" data-bs-placement="bottom" data-bs-content="Marcar para selecionar o vendedor no PDV para ser comissionado!" data-bs-original-title="" title="">
            <i class="m-1 bx bx-info-circle"></i>
        </label>';

        @endphp
        {!! Form::select('caixa_livre', 'Caixa livre' . $appendAttr, [1 => 'Sim', 0 => 'Não'])->attrs([
        'class' => 'select2',
        ]) !!}
    </div>

    <div class="col-md-3 mt-2">
        @php
        $appendAttr = '<label class="text-info label-popover" data-bs-container="body" data-bs-toggle="popover" data-bs-placement="bottom" data-bs-content="Marcar para usuário conceder desconto no PDV e pedido" data-bs-original-title="" title="">
            <i class="m-1 bx bx-info-circle"></i>
        </label>';

        @endphp
        {!! Form::select('permite_desconto', 'Permite desconto' . $appendAttr, [1 => 'Sim', 0 => 'Não'])->attrs([
        'class' => 'select2',
        ]) !!}
    </div>

    <hr>
    @isset($usuario)
    {!! __view_locais_user_edit($usuario->locais) !!}
    @else
    {!! __view_locais_user() !!}
    @endif

    <div class="col-md-3 col-12">
        @isset($usuario)
        <label>Local padrão</label>
        <select id="locais" name="local_padrao" class="form-select">
            <option value="0">--</option>
            @foreach(__locaisAtivosAll() as $key => $l)
            <option @isset($usuario) @if($usuario->local_padrao == $key) selected @endif @endif value="{{$key}}">{{$l}}</option>
            @endforeach
        </select>
        @else
        <label for="">Local padrão</label>
        <select id="locais" name="local_padrao" class="form-select">
            <option value="0">--</option>
            @foreach(__locaisAtivosAll() as $key => $l)
            <option value="{{$key}}">{{$l}}</option>
            @endforeach
        </select>
        @endisset
    </div>

    <hr>

    <div class="col-12 mt-4">
        @if (!isset($not_submit))
        <div id="image-preview" class="_image-preview col-md-4">
            <label for="image-upload" id="image-label">Selecione a imagem</label>
            <input type="file" name="image" id="image-upload" class="_image-upload" accept="image/*" />
            @isset($usuario)
            @if($usuario->img)
            <img src="/uploads/usuarios/{{ $usuario->img }}" class="img-default">
            @else
            <img src="/imgs/no_image.png" class="img-default">
            @endif
            @else
            <img src="/imgs/no_image.png" class="img-default">
            @endif
        </div>
        @endif
    </div>

    <hr>

    <h6>Permissão de Acesso:</h6>
    <input type="hidden" id="menus" value="{{json_encode($menu)}}" name="">
    @foreach($menuAux as $m)
    @if($m['ativo'] == 1)
    <div class="col-12 col-form-label">
        <span>
            <label class="checkbox checkbox-info">
                <input class="check-all todos_{{str_replace(' ', '', $m['titulo'])}}" value="{{$m['titulo']}}" type="checkbox">
                <span></span><strong class="text-info" style="margin-left: 5px; font-size: 16px;">{{$m['titulo']}}</strong>
            </label>
        </span>
        <div class="checkbox-inline" style="margin-top: 10px;">
            @foreach($m['subs'] as $s)
            @if(in_array($s['rota'], $permissoesAtivas))
            <label class="checkbox checkbox-info check-sub">
                <input class="{{str_replace(' ', '', $m['titulo'])}}" @if(in_array($s['rota'], $permissoesUsuario)) checked @endif type="checkbox" name="{{$s['rota']}}">
                <span></span>{{$s['nome']}}
            </label>
            @endif
            @endforeach
        </div>
    </div>

    @endif
    @endforeach
    <div class="col-12">
        <button type="submit" class="btn btn-primary px-5">Salvar</button>
    </div>
</div>

@section('js')
<script type="text/javascript">
    $(function() {
        $('[data-bs-toggle="popover"]').popover();

        @isset($usuario)
        $('#inp-senha').removeAttr('required')
        @endif
    });

</script>

<script type="text/javascript" src="/js/usuario.js"></script>
<script type="text/javascript" src="/assets/js/jquery.uploadPreview.min.js"></script>

@endsection
