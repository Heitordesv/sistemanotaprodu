@extends('default.layout',['title' => 'Controle de pedidos'])
@section('content')
<div class="page-content">
    <div class="card border-top border-0 border-4 border-primary">
        <div class="card-body p-4">
            <div class="row">
                <h3 class="center-align">Controle de Pedidos <strong> - {{$tela}}</strong>
                    <a href="{{ route('controleCozinha.selecionar') }}" class="btn btn-danger">voltar</a>
                </h3>
                <div class="col-sm-6 col-lg-6 col-md-6 col-xl-6">
                    <div class="progresso" style="display: none">
                        <div class="spinner spinner-track spinner-primary spinner-lg mr-15"></div>
                    </div>
                </div>
            </div>
            <input type="hidden" value="{{$id}}" id="tela" name="">
            <div class="row mt-5" id="itens">
            </div>
        </div>
    </div>
</div>

@section('js')
<script>
    $(function() {
        buscar()
        setInterval(() => {
            buscar()

        }, 5000);
    })

    function buscar() {
        $.get('{{ route("controleCozinha.buscar")}}', {
            tela: $('#tela').val()
            , empresa_id: $("#empresa_id").val()
        }).done((e) => {
            console.log(e)
            $('#itens').html(e)
        }).fail((e) => {
            console.log(e)
        })
    }

</script>
@endsection
@endsection
