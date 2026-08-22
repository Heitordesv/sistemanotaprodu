<div class="card border-0 shadow-sm mb-4" id="secao-identidade">
    <div class="card-header bg-white border-bottom py-3">
        <div class="d-flex align-items-center gap-3">
            <div class="rounded-circle bg-light-primary text-primary d-flex align-items-center justify-content-center" style="width:42px;height:42px;">
                <i class="bx bx-store-alt fs-4"></i>
            </div>
            <div>
                <h5 class="mb-0">Dados da Loja</h5>
                <small class="text-muted">Identificação pública, funcionamento e redes sociais.</small>
            </div>
        </div>
    </div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-6">
                {!! Form::text('nome', 'Nome de Exibição')->required() !!}
                <small class="text-muted">Exemplo: Minha Loja</small>
            </div>
            <div class="col-md-6">
                {!! Form::text('link', 'Link da Loja')->required() !!}
                <small class="text-muted">Exemplo: minha-loja</small>
            </div>

            <div class="col-12">
                {!! Form::text('funcionamento', 'Horário / Funcionamento')->required() !!}
                <small class="text-muted">Exemplo: Segunda a sexta, das 08:00 às 18:00.</small>
            </div>

            <div class="col-md-4">
                {!! Form::text('link_instagram', 'Instagram') !!}
            </div>
            <div class="col-md-4">
                {!! Form::text('link_facebook', 'Facebook') !!}
            </div>
            <div class="col-md-4">
                {!! Form::text('link_twiter', 'X / Twitter') !!}
            </div>
        </div>
    </div>
</div>