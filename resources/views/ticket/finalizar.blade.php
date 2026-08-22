@extends('default.layout',['title' => 'Finalizar Ticket'])
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
            <div class="card-title d-flex align-items-center">
                <h5 class="mb-0 text-primary">Finalizar Ticket: <strong>{{$item->id}}</strong></h5>
            </div>
            <hr>
            {!!Form::open()
            ->post()
            ->route('tickets.finalizar')
            !!}
            <div class="pl-lg-4">
            <input type="hidden" name="ticket_id" value="{{$item->id}}">
                <div class="col-md-12">
                    {!!Form::text('mensagem_finalizar', 'Mensagem')
                    !!}
                </div>
                <div class="col-12 mt-3">
                    <button class="btn btn-info px-5">Salvar</button>
                </div>
            </div>
            {!!Form::close()!!}
        </div>
    </div>
</div>
@endsection
