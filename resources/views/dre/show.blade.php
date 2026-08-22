@extends('default.layout',['title' => 'Dre'])
@section('content')
<div class="page-content">
    <div class="card ">
        <div class="card-body p-4">
            <div class="" id="kt_user_profile_aside" style="margin-left: 10px; margin-right: 10px;">
                <div class="col-lg-12" id="content">
                    <!--begin::Portlet-->
                    <br>
                    <div class="row">
                        <div class="col-lg-4">
                            <h4 class="card-title">Inicio:
                                <strong class="text-success">{{__data_pt($item->inicio)}}
                                </strong>
                            </h4>
                        </div>

                        <div class="col-lg-4">
                            <h4 class="card-title">Fim:
                                <strong class="text-danger">{{__data_pt($item->fim)}}
                                </strong>
                            </h4>
                        </div>

                        @if($tributacao->regime != 1)
                        <div class="col-lg-4">
                            <h4 class="card-title">% Imposto:
                                <strong class="text-primary">{{__moeda($item->percentual_imposto)}}
                                </strong>
                            </h4>
                        </div>
                        @endif
                    </div>

                    <div class="row">
                        <div class="col-lg-12">
                            <h4 class="card-title">Observação:
                                <strong class="text-info">
                                    {{ $item->observacao != "" ? $item->observacao : "--" }}
                                </strong>
                            </h4>
                        </div>
                    </div>

                    <div class="card card-custom gutter-b">
                        <div class="card-body">
                            @foreach($item->categorias as $key => $c)
                            <div class="card card-custom gutter-b example example-compact">
                                <div class="card-header bg-info">
                                    <h3 class="card-title text-white">{{$c->nome}}
                                        <button style="margin-left: 5px;" class="btn btn-sm btn-success" onclick="addLancamento({{$c}})">
                                            <i class="bx bx-plus"></i>
                                        </button>
                                    </h3>
                                </div>

                                <div class="card-body">
                                    @foreach($c->lancamentos as $l)
                                    <div class="row" style="height: 35px;">
                                        <div class="col-6">
                                            <h5 class="text-left">{{$l->nome}}</h5>
                                        </div>

                                        <div class="col-4">
                                            <h5 class="text-left">
                                                <strong>
                                                    R$ {{ __moeda($l->valor) }}
                                                </strong>
                                                @if($key > 0)
                                                <strong class="text-danger">
                                                    - {{ __moeda($l->percentual) }}%
                                                </strong>
                                                @endif
                                            </h5>
                                        </div>
                                        <div class="col-2">
                                            <button onclick="editLancamento({{$l}})" style="margin-top: -5px;" class="btn btn-sm btn-outline-info" href="{{ route('dre.updatelancamento') }}">
                                                <i class="bx bx-edit"></i>
                                            </button>
                                            <a style="margin-top: -5px;" class="btn btn-sm btn-outline-danger" onclick='swal("Atenção!", "Deseja remover este registro?", "warning").then((sim) => {if(sim){ location.href="/dre/deleteLancamento/{{$l->id}}" }else{return false} })' href="#!">
                                                <i class="bx bx-trash"></i>
                                            </a>
                                        </div>
                                    </div>
                                    @endforeach

                                    @if($key > 2)

                                    <div class="row" style="height: 30px;">
                                        <div class="col-6">
                                            <h4 class="text-left text-info">{{$c->nome}}</h4>
                                        </div>
                                        <div class="col-4">
                                            <h5 class="text-left text-info">
                                                <strong>
                                                    R$ {{ __moeda($c->soma()) }}
                                                </strong>
                                                @if($key > 0)
                                                <strong class="text-primary">
                                                    - {{ __moeda($c->percentual()) }}%
                                                </strong>
                                                @endif
                                            </h5>
                                        </div>
                                    </div>
                                    @endif
                                </div>
                            </div>
                            @endforeach
                            <div class="card card-custom gutter-b">
                                <div class="card-body @if($item->lucro_prejuizo >= 0) bg-success @else bg-danger @endif">
                                    <div class="row" style="height: 45px;">
                                        <div class="col-6">
                                            <h4 class="text-left text-white">Lucro (Prejuizo) no Período</h4>
                                        </div>
                                        <div class="col-4">
                                            <h4 class="text-left text-white">
                                                <strong>
                                                    R$ {{ __moeda($item->lucro_prejuizo) }}
                                                </strong>
                                            </h4>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <a class="btn btn-info btn-lg" href="{{ route('dre.imprimir', $item->id) }}">
                        <i class="bx bx-printer"></i>
                        Imprimir
                    </a>
                </div>
            </div>

            <div class="modal fade" id="modal-edit" data-backdrop="static" tabindex="-1" role="dialog" aria-labelledby="staticBackdrop" aria-hidden="true">
                <div class="modal-dialog modal-sm" role="document">
                    <form method="post" action="{{ route('dre.updatelancamento') }}">
                        @csrf
                        <div class="modal-content">
                            <div class="modal-header">
                                <h6 id="titulo" class="modal-title"></h6>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div class="row">
                                    <div class="form-group validated col-sm-12 col-lg-12 col-12">
                                        <label class="col-form-label" id="">Nome</label>
                                        <input required="" type="text" placeholder="Nome" id="nome-edit" name="nome" class="form-control" value="">
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="form-group validated col-sm-6 col-lg-6 col-12">
                                        <label class="col-form-label" id="">Valor</label>
                                        <input type="text" placeholder="Valor" id="valor" name="valor" class="form-control moeda" value="">
                                    </div>
                                </div>
                                <input type="hidden" id="lancamento_id" name="lancamento_id">
                            </div>
                            <div class="modal-footer">
                                <button style="width: 100%" type="submit" class="btn btn-success font-weight-bold">
                                    <i class="bx bx-edit"></i>
                                    ALTERAR
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="modal fade" id="modal-new" data-backdrop="static" tabindex="-1" role="dialog" aria-labelledby="staticBackdrop" aria-hidden="true">
                <div class="modal-dialog modal-sm" role="document">
                    <form method="post" action="{{ route('dre.novolancamento') }}">
                        @csrf
                        <div class="modal-content">
                            <div class="modal-header">
                                <h6 id="titulo-new" class="modal-title"></h6>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div class="row">
                                    <div class="form-group validated col-sm-12 col-lg-12 col-12">
                                        <label class="col-form-label" id="">Nome</label>
                                        <input required="" type="text" placeholder="Nome" id="nome" name="nome" class="form-control" value="">
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="form-group validated col-sm-6 col-lg-6 col-12">
                                        <label class="col-form-label" id="">Valor</label>
                                        <input required type="text" placeholder="Valor" id="valor" name="valor" class="form-control moeda" value="">
                                    </div>
                                </div>
                                <input type="hidden" id="categoria_id" name="categoria_id">
                            </div>
                            <div class="modal-footer">
                                <button style="width: 100%" type="submit" class="btn btn-success font-weight-bold">
                                    <i class="la la-check"></i>
                                    SALVAR
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@section('js')
    <script src="/js/dre.js"></script>
@endsection
@endsection
