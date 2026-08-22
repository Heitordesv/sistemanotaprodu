<div class="card border-0 shadow-sm mb-4" id="secao-conteudo">
    <div class="card-header bg-white border-bottom py-3">
        <div class="d-flex align-items-center gap-3">
            <div class="rounded-circle bg-light-secondary text-secondary d-flex align-items-center justify-content-center" style="width:42px;height:42px;">
                <i class="bx bx-file fs-4"></i>
            </div>
            <div>
                <h5 class="mb-0">Conteúdo, Mapa e Privacidade</h5>
                <small class="text-muted">Textos institucionais e configurações de localização.</small>
            </div>
        </div>
    </div>

    <div class="card-body">
        <div class="row g-3">
            <div class="col-12">
                {!! Form::textarea('politica_privacidade', 'Política de Privacidade') !!}
                <small class="text-muted">Informe como os dados dos clientes são tratados na loja.</small>
            </div>

            <div class="col-md-6">
                {!! Form::text('google_api', 'Google Maps API') !!}
                <small class="text-muted">Opcional. Use quando recursos de mapa exigirem sua chave.</small>
            </div>

            <div class="col-md-6">
                <label class="form-label">Mapa incorporado</label>
                <textarea name="src_mapa" class="form-control" rows="3" placeholder="Cole somente o valor do src do iframe">{{ old('src_mapa', $item->src_mapa ?? '') }}</textarea>
                <small class="text-muted">Cole somente o conteúdo do atributo <code>src</code>, não o iframe inteiro.</small>
            </div>
        </div>
    </div>
</div>