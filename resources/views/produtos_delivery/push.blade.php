@extends('default.layout',['title' => 'Novo Push'])
@section('content')
<div class="page-content">
    <div class="card border-top border-0 border-4 border-primary">
        <div class="card-body p-5">
            <div class="page-breadcrumb d-sm-flex align-items-center mb-3">
                <div class="ms-auto">
                    <a href="{{ route('produtoDelivery.index')}}" type="button" class="btn btn-light btn-sm">
                        <i class="bx bx-arrow-back"></i> Voltar
                    </a>
                </div>
            </div>
            <div class="card-title d-flex align-items-center">
                <h5 class="mb-0 text-primary">Cadastrar notificação push</h5>
            </div>
            <hr>
            {!!Form::open()
            // ->post()
            // ->route('produtoDelivery.store')
            // ->multipart()
            !!}
            <div class="pl-lg-4">
                <div class="row g-3">
                    <div class="col-md-9">
                        {!! Form::text('titulo', 'Título')->attrs(['class' => '']) !!}
                    </div>
                    <div class="col-md-9">
                        {!! Form::text('texto', 'Texto')->attrs(['class' => '']) !!}
                    </div>
                    <div class="col-md-6">
                        {!! Form::text('path_img', 'Endereço da imagem')->attrs(['class' => '']) !!}
                    </div>
                    <div class="col-md-3">
                        {!! Form::text('referencia_produto', 'Código de produto (opcional)')->attrs(['class' => '']) !!}
                    </div>
                    <div class="col-md-4">
                        {!! Form::select('clientes', 'Todos os clientes', [1 => 'Sim', 0 => 'Não'])->attrs(['class' => 'form-select']) !!}
                    </div>
                    <div class="col-md-6 cliente d-none">
                        {!! Form::select('cliente_id', 'Cliente')->attrs(['class' => 'select2']) !!}
                    </div>
                    <div class="col-12 mt-4">
                        <button class="btn btn-info px-5">Salvar</button>
                    </div>
                </div>
            </div>
            {!!Form::close()!!}
        </div>
    </div>
</div>
@endsection

@section('js')
<script>    
    $('#inp-clientes').change(() => {
        cliente()
    })
    function cliente() {
        let c = $('#inp-clientes').val()
        if (c == 1) {
            $('.cliente').addClass('d-none')
        } else {
            $('.cliente').removeClass('d-none')
        }
    }
</script>
@endsection
