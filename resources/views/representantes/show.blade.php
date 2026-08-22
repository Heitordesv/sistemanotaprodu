@extends('default.layout', ['title' => 'Detalhes Representantes'])
@section('content')
<div class="page-content">
    <div class="card border-top border-0 border-4 border-primary">
        <div class="card-body p-5">
            <div class="page-breadcrumb d-sm-flex align-items-center mb-3">
                <div class="ms-auto">
                    <a href="{{ route('representantes.index') }}" type="button" class="btn btn-light btn-sm">
                        <i class="bx bx-arrow-back"></i> Voltar
                    </a>
                </div>
            </div>
            <div class="card-title d-flex align-items-center">
                <h5 class="mb-0 text-primary">Detalhes</h5>
            </div>
            <hr>

            {!! Form::open()->fill($item)
            ->put()
            ->route('representantes.update', [$item->id])
            ->multipart() !!}
            <div class="pl-lg-4">
                @include('representantes._forms')
            </div>
            {!! Form::close() !!}

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
    </div>
</div>

@section('js')
<script type="text/javascript" src="/js/representante.js"></script>
@endsection

@endsection
