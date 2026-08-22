@section('css')
<style type="text/css">
    #map{
        width: 100%;
        height: 450px;
        background: #999;
    }

    .input-group-text:hover{
        cursor: pointer;
    }
</style>
@endsection

@isset($item)
<div class="row">
    <div class="col-lg-12">
        <button data-toggle="modal" data-target="#modal-local" type="button" class="btn btn-primary btn-local">
            <i class="bx bx-map"></i> Informar localização
        </button>

        @if($item->latitude != "")
        <br><br>
        <h5>Latitude: <strong class="lat">{{$item->latitude}}</strong></h5>
        <h5>Longitude: <strong class="lng">{{$item->longitude}}</strong></h5>
        @endif
    </div>
</div>
@endif

<div class="row card-body p-4">
    <div class="m-2">
       <a class="btn btn-primary" href="{{ route('configDelivery.galeria') }}"> <i class="bx bx-image"></i>Galeria de fotos</a>
    </div>
    <div class="col-md-4 mt-2">
        {!! Form::text('nome', 'Nome do delivery')->required() !!}
    </div>
    <div class="col-md-8 mt-2">
        {!! Form::text('descricao', 'Descrição')->required() !!}
    </div>
    <div class="col-md-6 mt-3">
        {!! Form::text('rua', 'Rua')->required() !!}
    </div>
    <div class="col-md-2 mt-3">
        {!! Form::tel('numero', 'Número')->required() !!}
    </div>
    <div class="col-md-4 mt-3">
        {!! Form::text('bairro', 'Bairro')->required() !!}
    </div>
    <div class="col-md-5 mt-3">
        {!! Form::select('cidade', 'Cidade', ['' => 'Selecione'] + $cidades->pluck('nome', 'id')->all())->attrs(['class' => 'form-select'])->value(isset($item) ? $item->cidade->id : '')->required() !!}
    </div>
    <div class="col-md-2 mt-3">
        {!! Form::tel('cep', 'Cep')->attrs(['class' => 'cep'])->required() !!}
    </div>
    <div class="col-md-2 mt-3">
        {!! Form::tel('telefone', 'Celular')->attrs(['class' => 'fone'])->required() !!}
    </div>
    <div class="col-md-3 mt-3">
        {!! Form::tel('tempo_medio_entrega', 'Tempo médio de entrega (min)')->required()->attrs(['data-mask' => '000']) !!}
    </div>
    <div class="col-md-3 mt-3">
        {!! Form::tel('valor_entrega', 'Valor de entrega padrão')->required()->attrs(['class' => 'moeda']) !!}
    </div>
    <div class="col-md-3 mt-3">
        {!! Form::tel('pedido_minimo', 'Pedido mínimo')->attrs(['class' => 'moeda']) !!}
    </div>
    <div class="col-md-3 mt-3">
        {!! Form::tel('maximo_adicionais', 'Máximo de adicionais')->required()->attrs(['data-mask' => '00']) !!}
    </div>
    <div class="col-md-3 mt-3">
        {!! Form::tel('maximo_adicionais_pizza', 'Máximo de adicionais pizza')->required()->attrs(['data-mask' => '00']) !!}
    </div>
    <div class="col-md-3 mt-3">
        {!! Form::tel('maximo_sabores_pizza', 'Máxima sabores de pizza')->attrs(['data-mask' => '0']) !!}
    </div>
    <div class="col-md-3 mt-3">
        {!! Form::select('tipo_divisao_pizza', 'Tipo divisão pizza', [0 => 'Divide valor', 1 => 'Maior valor'])->attrs(['class' => 'form-select']) !!}
    </div>

    <div class="col-md-3 mt-3">
        {!! Form::tel('tempo_maximo_cancelamento', 'Tempo para cancelamento HH:mm')->required()->attrs(['data-mask' => '00:00']) !!}
    </div>
    <div class="col-md-3 mt-3">
        {!! Form::select('tipo_entrega', 'Tipo de entrega', [null => '---'] + [0 => 'Balcão e delivery', 1 => 'Somente balcão', 2 => 'Somente delivery'])->required()->attrs(['class' => 'form-select']) !!}
    </div>
    <div class="col-md-6 mt-3">
        {!! Form::text('mercadopago_public_key', 'Mercado pago public key') !!}
    </div>
    <div class="col-md-6 mt-3">
        {!! Form::text('mercadopago_access_token', 'Mercado pago access token') !!}
    </div>

    <div class="col-md-2 mt-3">
        {!! Form::select('autenticacao_sms', 'Autenticação SMS', [0 => 'Não', 1 => 'Sim'])->attrs(['class' => 'form-select']) !!}
    </div>
    <div class="col-md-2 mt-3">
        {!! Form::select('confirmacao_pedido_cliente', 'Confirmação pedido cliente', [0 => 'Não', 1 => 'Sim'])->attrs(['class' => 'form-select']) !!}
    </div>

    <div class="col-md-12 mt-3">
        {!! Form::textarea('politica_privacidade', 'Política de privacidade') !!}
    </div>
    <div class="col-md-4 mt-3">
        <label for="">API token</label>
        <div class="input-group">
            <input readonly id="api_token" value="{{ isset($item) ? $item->api_token : '' }}" name="api_token" type="text" class="form-control">
            <button type="button" class="btn btn-info" id="btn_token"><a class="bx bx-refresh"></a></button>
        </div>
    </div>
    <div class="card m-3">
        <p class="mt-3">Tipo de pagamento a serem exibidos:</p>
        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr;" class="m-1">
            @foreach(App\Models\DeliveryConfig::tiposPagamento() as $key => $t)
            <label class="">
                <input type="checkbox" name="tipos_pagamento[]" value="{{$key}}" @if($item !=null) @if(sizeof($item->tipos_pagamento) > 0 && in_array($key, $item->tipos_pagamento)) checked="true" @endif @endif>
                {{$t}}
            </label>
            @endforeach
        </div>
    </div>
    <div class="col-md-4 mt-3">
        <label for="">Logo 60 x 60</label>

        <div id="image-preview" class="_image-preview col-md-4">
            <label for="" id="image-label" class="_image-label">Selecione a imagem</label>
            
            <input type="file" name="image" id="image-upload" class="_image-upload" accept="image/*" />
            @isset($item)
            @if ($item->logo)
            <img src="/delivery/logos/{{ $item->logo }}" class="img-default">
            @else
            <img src="/imgs/no_image.png" class="img-default">
            @endif
            @else
            <img src="/imgs/no_image.png" class="img-default">
            @endif
        </div>

        @if($errors->has('image'))
        <div class="text-danger mt-2">
            {{ $errors->first('image') }}
        </div>
        @endif
    </div>
    <div class="col-12 mt-4">
        @isset($not_submit)
        <button type="button" class="btn btn-primary px-5" id="">Salvar</button>
        @else
        <button type="submit" class="btn btn-primary px-5">Salvar</button>
        @endif
    </div>
</div>


@section('js')
<script src="https://maps.googleapis.com/maps/api/js?key={{env('API_KEY_MAPS')}}"
async defer></script>
<script type="text/javascript" src="/assets/js/jquery.uploadPreview.min.js"></script>
<script>

    $('.btn-local').click(() => {
        $('#modal-local').modal('show')
    })

    $('#btn_token').click(() => {
        let token = generate_token(25);
        swal({
            title: "Atenção",
            text: "Esse token é o responsavel pela comunicação com o ecommerce, tenha atenção!!",
            icon: "warning",
            buttons: true,
            dangerMode: true,
        }).then((confirmed) => {
            if (confirmed) {
                $('#api_token').val(token)
            }
        });
    })

    function generate_token(length) {
        var a = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890".split("");
        var b = [];
        for (var i = 0; i < length; i++) {
            var j = (Math.random() * (a.length - 1)).toFixed(0);
            b[i] = a[j];
        }
        return b.join("");
    }

    $(function(){
        let lng = $('.lng').html()
        let lat = $('.lat').html()
        if(lng && lat){
            initMap(lat, lng);
        }else{
            getCurrentLocation((crd) => {
                if(crd){
                    initMap(crd.latitude, crd.longitude);
                }else{
                    swal("Atenção!", 'Não foi possivel recuperar sua localização, ative e recarregue a pagina!', "warning")
                }
            })
        }
    })

    function getCurrentLocation(call){
        var options = {
            enableHighAccuracy: true,
            timeout: 5000,
            maximumAge: 0
        };

        function success(pos) {
            var crd = pos.coords;
            call(crd);
        };

        function error(err) {
            console.warn('ERROR(' + err.code + '): ' + err.message);
            call(false)
        };

        navigator.geolocation.getCurrentPosition(success, error, options);
    }

    function initMap(lat, lng){

        $('#lat').val(lat)
        $('#lng').val(lng)
        const position = new google.maps.LatLng(lat, lng);

        var map = new google.maps.Map(document.getElementById('map'), {
            zoom: 16,
            center: position,
            disableDefaultUI: false
        }); 


        const marker = new google.maps.Marker({
            position: position,
            map: map,
            animation: google.maps.Animation.BOUNCE,
            draggable: true
        })

        // getEnderecoByCoords(lat, lng, (res) => {
        //  if(res == false){

        //  }else{
        //      $('#rua').val(res.rua)
        //      $('#numero').val(res.numero)
        //      validaCamposNovoEndereco();
        //  }
        // })

        google.maps.event.addListener(marker, 'dragend', (event) => {
            var myLatLng = event.latLng;
            var lat = myLatLng.lat();
            var lng = myLatLng.lng();

            $('#lat').val(lat)
            $('#lng').val(lng)
        })

    }
</script>

@endsection
