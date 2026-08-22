@extends('ecommerce_one_tech.default')
@section('content')

<div class="container">

    <div class="contact_form">
        <div class="container">
            <div class="row">
                <div class="col-lg-6 offset-lg-3">
                    <div class="contact_form_container">
                        <div class="contact_form_title">
                            <center>Informe suas credenciais</center>
                        </div>

                        <form method="post" id="contact_form">
                            @csrf
                            <input type="hidden" value="{{$default['config']->empresa_id}}" name="empresa_id">
                            <div class="contact_form_inputs d-flex">
                                <input style="width: 100%" type="text" id="contact_form_name" class="contact_form_name input_field" name="email" placeholder="Email">
                            </div>

                            <div class="contact_form_inputs d-flex">
                                <input style="width: 100%" type="password" id="contact_form_name" class="contact_form_name input_field" name="senha" placeholder="Senha">
                                
                            </div>

                            <div class="contact_form_button">
                                <button style="width: 100%" type="submit" class="button contact_submit_button">Login</button>
                            </div>

                            <div class="row">
                                <div class="col-lg-12" style="margin-top: 5px; margin-bottom: 5px;">
                                    <a href="{{$rota}}/esquecisenha" class="btn btn-outline-danger" href="">Esqueci minha senha</a>
                                </div>
                            </div>
                        </form>

                        @if($default['config']->politica_privacidade != "")
                        <div class="row">
                            <div class="col-lg-12" style="margin-top: 10px;">
                                <center>
                                    <a class="text-info" data-toggle="modal" data-target="#modal-politica">Politica de privacidade</a>
                                </center>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<br>

<div class="modal fade" id="modal-politica" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Politica de privacidade</h5>
            </div>
            <div class="modal-body">

                {{$default['config']->politica_privacidade}}

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-dismiss="modal">Fechar</button>
            </div>

        </div>
    </div>
</div>
@endsection 
