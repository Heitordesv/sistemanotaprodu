@extends('ecommerce_one_tech.default')
@section('content')

<style type="text/css">
    .is-invalid{
        border: 1px solid red;
    }
    .is-invalid::placeholder {
      color: red;
  }
</style>

<div class="super_container">

    <div class="contact_info">
        <div class="container">
            <div class="row">
                <div class="col-lg-10 offset-lg-1">
                    <div class="contact_info_container d-flex flex-lg-row flex-column justify-content-between align-items-between">

                        <!-- Contact Item -->
                        <div class="contact_info_item d-flex flex-row align-items-center justify-content-start">
                            <div class="contact_info_image"><img src="/ecommerce_one/images/contact_1.png" alt=""></div>
                            <div class="contact_info_content">
                                <div class="contact_info_title">Telefone</div>
                                <div class="contact_info_text">{{$default['config']->telefone}}</div>
                            </div>
                        </div>

                        <!-- Contact Item -->
                        <div class="contact_info_item d-flex flex-row align-items-center justify-content-start">
                            <div class="contact_info_image"><img src="/ecommerce_one/images/contact_2.png" alt=""></div>
                            <div class="contact_info_content">
                                <div class="contact_info_title">Email</div>
                                <div class="contact_info_text">{{$default['config']->email}}</div>
                            </div>
                        </div>

                        <!-- Contact Item -->
                        <div class="contact_info_item d-flex flex-row align-items-center justify-content-start">
                            <div class="contact_info_image"><img src="/ecommerce_one/images/contact_3.png" alt=""></div>
                            <div class="contact_info_content">
                                <div class="contact_info_title">Endereço</div>
                                <div class="contact_info_text">
                                    {{$default['config']->rua}}, {{$default['config']->numero}}
                                    - {{$default['config']->bairro}}
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="contact_form">
        <div class="container">
            <div class="row">
                <div class="col-lg-10 offset-lg-1">
                    <div class="contact_form_container">

                        <div class="contact_form_title">Deixe uma mensagem</div>

                        @if(sizeof($errors) > 0)
                        <div class="container">
                            <div class="alert alert-custom alert-danger fade show" role="alert" style="margin-top: 10px;">

                                <div class="alert-text"><i class="fa fa-check"></i> 
                                    Erro no forumulário
                                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                        <i class="fa fa-close"></i>
                                    </button>
                                </div>

                            </div>
                        </div>
                        @endif

                        <form method="post" action="/ecommerceContato">
                            @csrf
                            <div class="contact_form_inputs align-items-between">
                                <input type="hidden" value="{{$default['config']->empresa_id}}" name="empresa_id">
                                
                                <input @if(sizeof($errors) > 0) autofocus @endif type="text" id="contact_form_name" name="nome" class="contact_form_name input_field @if($errors->has('nome')) is-invalid @endif" placeholder="Nome" value="{{old('nome')}}">


                                <input type="email" name="email" class="contact_form_name input_field @if($errors->has('email')) is-invalid @endif" placeholder="Email" value="{{old('email')}}">
                                

                            </div>
                            <div class="contact_form_text">
                                <textarea name="texto" id="contact_form_message" class="text_field contact_form_message @if($errors->has('texto')) is-invalid @endif" name="message" rows="4" placeholder="Menssagem">{{old('texto')}}</textarea>
                                @if($errors->has('texto'))
                                <span class="text-danger">{{ $errors->first('texto') }}</span>
                                @endif
                            </div>
                            <div class="contact_form_button">
                                <button type="submit" class="button contact_submit_button">Enviar</button>
                            </div>
                        </form>

                    </div>
                </div>
            </div>
        </div>
        <div class="panel"></div>
    </div>

    <input type="hidden" value="{{$default['config']->latitude}}" id="lat">
    <input type="hidden" value="{{$default['config']->longitude}}" id="lng">
    <div class="contact_map">
        <div id="google_map" class="google_map">
            <div class="map_container">
                <div id="map"></div>
            </div>
        </div>
    </div>

</div>

@endsection 
