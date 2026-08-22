@extends('default.layout',['title' => 'Ticket'])
@section('content')
<div class="page-content">
    <div class="card border-top border-0 border-4 border-primary">
        <div class="card-body p-5">
            <div class="page-breadcrumb d-sm-flex align-items-center mb-3">
                <div class="ms-auto">
                    <a href="{{ route('tickets.index')}}" type="button" class="btn btn-light btn-sm">
                        <i class="bx bx-arrow-back"></i> Voltar
                    </a>
                </div>
            </div>
            <hr>
            <div class="card">
                <div class="card-title d-flex align-items-center m-3">
                    <h5 class="mb-0 text-primary">Ticket: <strong> {{ $item->id }}</strong></h5>
                    <a class="btn btn-danger m-1" href="{{ route('ticketsSuper.finalizar', $item->id) }}">
                        <i class="bx bx-x"></i> Finalizar Ticket
                    </a>
                    <h5 class="ms-auto">Estado: </h5>
                    @if($item->estado == 'aberto')
                    <strong class="btn btn-warning m-1">ABERTO</strong>
                    @elseif($item->estado == 'respondida')
                    <strong class="btn btn-info m-1">RESPONDIDA</strong>
                    @else
                    <strong class="btn btn-success m-1">FINALIZADO</strong>
                    @endif
                </div>
            </div>

            <div class="card">
                <div class="card-title d-flex m-3">
                    <h5>Assunto: {{$item->assunto}}</h5>
                    <h5 class="ms-auto">Departamento: {{$item->departamento == '1' ? 'Suporte' : 'Conta e Vendas'}}</h5>
                </div>
            </div>

            @if($item->estado == 'finalizado')
            <div class="card mt-3" style="background: #f0e87b; height: 120px; margin-top: -25px">
                <div class="container">
                    <div class="row" style="margin-top: 15px;">
                        <h4 class="alert-text" style="color: crimson">
                            Não é possível efetuar novas interações!<br>
                            <strong>{{$item->mensagem_finalizar}}</strong>
                        </h4>
                    </div>
                </div>
            </div>
            @endif

            <div class="mt-4 mb-4">
                @foreach($item->mensagens as $m)
                    @php
                        $isSuporte = $m->mensagemSuper();
                    @endphp

                    <div class="d-flex mb-3 {{ $isSuporte ? 'justify-content-end' : 'justify-content-start' }}">
                        <div class="card border-0 shadow-sm mb-0"
                             style="width: 78%; max-width: 760px; border-radius: 18px; background: {{ $isSuporte ? '#e7f1ff' : '#f4f5f7' }};">
                            <div class="card-body px-3 py-2">
                                <div class="d-flex align-items-center gap-2 mb-2">
                                    <div class="d-flex align-items-center gap-1">
                                        <i class="bx bx-user-circle fs-5"></i>
                                        <strong>{{$m->usuario->nome}}</strong>
                                    </div>

                                    @if($isSuporte)
                                        <span class="badge bg-primary">Suporte</span>
                                    @else
                                        <span class="badge bg-secondary">Cliente</span>
                                    @endif

                                    <small class="text-muted ms-auto">
                                        {{\Carbon\Carbon::parse($m->created_at)->format('d/m/Y H:i')}}
                                    </small>
                                </div>

                                <div style="white-space: normal; overflow-wrap: anywhere;">
                                    {!! $m->mensagem !!}
                                </div>

                                @if($m->imagem != "")
                                    <div class="mt-2">
                                        <img class="img-fluid rounded" src="/uploads/ticket/{{$m->imagem}}" alt="Anexo da mensagem">
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <hr>

            {!!Form::open()
            ->post()
            ->route('tickets.novaMensagem')
            ->multipart()!!}

            <input type="hidden" name="ticket_id" value="{{$item->id}}">

            <div class="pl-lg-4">
                <div class="col-12">
                    {!!Form::textarea('mensagem', 'Nova Mensagem')!!}
                </div>

                <div class="col-12 mt-4">
                    @if (!isset($not_submit))
                    <div id="image-preview" class="_image-preview col-md-4">
                        <label for="" id="image-label" class="_image-label">Selecione a imagem</label>
                        <input type="file" name="image" id="image-upload" class="_image-upload" accept="image/*" />
                        @isset($item)
                            @if ($item->imagem)
                                <img src="/uploads/tickets/{{ $item->imagem }}" class="img-default">
                            @else
                                <img src="/imgs/no_image.png" class="img-default">
                            @endif
                        @else
                            <img src="/imgs/no_image.png" class="img-default">
                        @endif
                    </div>
                    @endif
                </div>

                <div class="col-12 mt-3">
                    <button class="btn btn-primary px-5" type="submit">Salvar</button>
                </div>
            </div>

            {!!Form::close()!!}
        </div>
    </div>
</div>

@section('js')
<script type="text/javascript" src="/assets/js/jquery.uploadPreview.min.js"></script>
@endsection

@endsection