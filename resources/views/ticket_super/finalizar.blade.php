@extends('default.layout',['title' => 'Finalizar Ticket'])
@section('content')
<div class="page-content">
    <div class="card border-top border-0 border-4 border-primary">
        <div class="card-body p-5">
            <div class="page-breadcrumb d-sm-flex align-items-center mb-3">
                <div class="ms-auto">
                    <a href="{{ route('ticketsSuper.index')}}" type="button" class="btn btn-light btn-sm">
                        <i class="bx bx-arrow-back"></i> Voltar
                    </a>
                </div>
            </div>
            <div class="card-title d-flex align-items-center">
                <h5 class="mb-0 text-primary">Finalizar Ticket</h5>
            </div>
            <hr>
            {!!Form::open()
            ->post()
            ->route('ticketsSuper.finalizarPost')
            !!}
            <div class="pl-lg-4">
                <div class="row g-3">
                    <input type="hidden" name="item" id="" value="{{$item->id}}">
                    <div>
                        {!!Form::text('mensagem_finalizar', 'Mensagem')!!}
                    </div>
                </div>
                <div class="mt-3">
                    <button type="submit" class="btn btn-info px-5">Salvar</button>
                </div>
            </div>
            {!!Form::close()!!}
        </div>
    </div>
</div>
@endsection
