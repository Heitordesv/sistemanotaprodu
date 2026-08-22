@extends('ecommerce_one_tech.default')
@section('content')

<div class="container">

    <div class="contact_form">
        <div class="container">
            <div class="row">
                <div class="col-lg-6 offset-lg-3">
                    <div class="contact_form_container">
                        <div class="contact_form_title">
                            <center>Esqueci minha senha</center>
                        </div>

                        <form method="post" id="contact_form">
                            @csrf
                            <div class="contact_form_inputs d-flex">
                                <input style="width: 100%" type="text" id="contact_form_name" class="contact_form_name input_field" name="email" placeholder="Email">
                                
                            </div>

                            <div class="contact_form_button">
                                <button style="width: 100%" type="submit" class="btn btn-danger">Recuperar</button>
                            </div>

                        </form>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<br>

@endsection 
