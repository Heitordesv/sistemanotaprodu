@section('css')
<style type="text/css">
    input[type='checkbox']:hover {
        cursor: pointer;
    }

</style>
@endsection
{{$errors}}
<div class="row g-3">
    <div class="col-md-4">
        {!! Form::text('cpf_cnpj', 'CNPJ')->attrs(['class' => 'cpf_cnpj'])->required() !!}
    </div>
    <div class="col-md-1 col-6"><br>
        <button class="btn btn-dark btn-block w-100" type="button" id="btn-consulta-cnpj">
            <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span><i class="bx bx-search"></i>
        </button>
    </div>

    <div class="col-md-6">
        {!! Form::text('razao_social', 'Razão social')->required() !!}
    </div>
    <div class="col-md-6">
        {!! Form::text('nome_fantasia', 'Nome fantasia')->required() !!}
    </div>

    <div class="col-md-6">
        {!! Form::text('rua', 'Rua')->required() !!}
    </div>
    <div class="col-md-2">
        {!! Form::text('numero', 'Número')->required()->attrs(['data-mask' => '0000000000']) !!}
    </div>
    <div class="col-md-4">
        {!! Form::text('bairro', 'Bairro')->required() !!}
    </div>
    <div class="col-md-2">
        {!! Form::tel('telefone', 'Telefone')->attrs(['class' => 'fone'])->required() !!}
    </div>
    @isset($empresa)
    <div class="col-md-4">
        {!! Form::select('cidade_id', 'Cidade')->required()->attrs(['class' => 'select2'])
        ->options($empresa != null ? [$empresa->cidade_id => $empresa->cidade->info] : [])
        !!}
    </div>
    @else
    <div class="col-md-4">
        {!! Form::select('cidade_id', 'Cidade')->attrs(['class' => 'select2'])
        !!}
    </div>
    @endif

    <div class="col-md-6">
        {!! Form::text('email', 'E-mail')->type('email')->required() !!}
    </div>
    <hr class="mt-4">
    @if(!isset($empresa))
    <div class="col-md-3">
        {!! Form::text('login', 'Login')->required() !!}
    </div>
    <div class="col-md-3">
        {!! Form::text('senha', 'Senha')->type('password')->attrs(['data-mask' => 'AAAAAAAAAA'])->required() !!}
    </div>
    <div class="col-md-3">
        {!! Form::text('usuario', 'Usuário')->required() !!}
    </div>
    @endif
    <div class="col-md-3">
        {!! Form::select('contador_id', 'Contador (Opcional)')->attrs(['class' => 'form-select']) !!}
    </div>
    <div class="col-md-3">
        @php
        $appendAttr = '<label class="text-info label-popover" data-bs-container="body" data-bs-toggle="popover" data-bs-placement="bottom" data-bs-content="Se selecionado a empresa será listada no cadastro de representantes" data-bs-original-title="" title="">
            <i class="bx bx-info-circle"></i>
        </label>';
        @endphp
        {!! Form::select('tipo_representante', 'Tipo Representante' . $appendAttr, [0 => 'Não', 1 => 'Sim'])->attrs([
        'class' => 'form-select',
        ]) !!}
    </div>
    <div class="col-md-4">
        {!! Form::select('acesso', 'Acesso a todos os Módulos', [0 => 'Não', 1 => 'Sim'])
        ->attrs(['class' => 'form-select select-all'])
        !!}
    </div>
    <div class="row mt-3">
        <div class="form-group validated">
            <label class="col-3 col-form-label">Permissão de Acesso:</label>
            @if (sizeof($perfis) > 0)
            <div class="form-group validated col-sm-4 col-lg-4">
                <label class="col-form-label" id="">Perfil</label>
                <div class="">
                    <select id="perfil-select" class="form-select" name="perfil_id">
                        <option value="0">--</option>
                        @foreach ($perfis as $p)
                        <option value="{{ $p }}">
                            {{ $p->nome }}
                        </option>
                        @endforeach
                    </select>
                </div>
            </div>
            @endif
            <input type="hidden" id="menus" value="{{ json_encode($menu) }}" name="">
            @foreach ($menu as $m)
            <div class="col-12 col-form-label mt-2">
                <span>
                    <label class="checkbox checkbox-info">
                        <input class="check-all todos_{{$m['titulo']}}" value="{{$m['titulo']}}" type="checkbox">
                        <span></span><strong class="text-info" style="margin-left: 5px; font-size: 16px;">{{$m['titulo']}}</strong>
                    </label>
                </span>
                <div class="checkbox-inline" style="margin-top: 10px;">
                    @foreach ($m['subs'] as $s)
                    @php
                    $link = str_replace('/', '_', $s['rota']);
                    $link = str_replace(':', '', $link);

                    @endphp
                    <label class="checkbox checkbox-info check-sub">
                        <input class="{{$m['titulo']}} {{$link}}" type="checkbox" name="{{ $s['rota'] }}" @isset($empresa) @if(in_array($s['rota'], $permissoesAtivas)) checked @endif @endisset>
                        <span></span>{{ $s['nome'] }}
                    </label>
                    @endforeach
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>
<hr>
<div class="align-items-center">
    <button type="submit" class="btn btn-primary px-5">Salvar</button>
</div>

@section('js')
<script type="text/javascript" src="/js/rep.js"></script>
@endsection
