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
                    <h5 class="mb-0 text-primary">Ticket: <strong> {{ $item->id }}</strong></h5> <a class="btn btn-danger m-1" href=" {{ route ('ticketsSuper.finalizar', $item->id ) }}"><i class="bx bx-x"></i>Finalizar Ticket</a>
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
                    <h5 class="">Assunto: {{$item->assunto}}</h5>
                    <h5 class="ms-auto">Departamento: {{$item->departamento == '1' ? 'Suporte' : 'Conta e Vendas'}}</h5>
                </div>
            </div>
            @if($item->estado == 'finalizado')
            <div class="card mt-3" style="background: #f0e87b; height: 120px; margin-top: -25px">
                <div class="container">
                    <div class="row" style="margin-top: 15px;">
                        <h4 class="alert-text" style="color: crimson">Não é possível efetuar novas interações!<br><strong class="">{{$item->mensagem_finalizar}}</strong></h4>
                    </div>
                </div>
            </div>
            @endif

            @foreach($item->mensagens as $m)
            <div class="">
                <div class="card card-body @if($m->mensagemSuper()) bg-light-success @endif">
                    <div class="row">
                        <div class="col-lg-6">
                            <i class="bx bx-user"></i>
                            {{$m->usuario->nome}}
                            @if($m->mensagemSuper())
                            - <strong class="btn btn-primary">suporte</strong>
                            @else
                            - <strong class="btn btn-primary">cliente</strong>
                            @endif
                        </div>
                        <div class="col-lg-6 text-right">
                            {{\Carbon\Carbon::parse($m->created_at)->format('d/m/Y (H:i)')}}
                        </div>
                    </div>
                    <hr>
                    {!! $m->mensagem !!}
                    @if($m->imagem != "")
                    <img style="width: 100%; height: auto;" src="/uploads/ticket/{{$m->imagem}}">
                    @endif
                </div>
            </div>
            @endforeach

            <hr>
            {!!Form::open()
            ->post()
            ->route('tickets.novaMensagem')
            ->multipart()!!}
            <input type="hidden" name="ticket_id" value="{{$item->id}}">
            <div class="pl-lg-4">
                <div class="col-12">
                    {!!Form::textarea('mensagem', 'Nova Mensagem')
                    !!}
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