<div class="card border-0 shadow-sm mb-4" id="secao-aparencia">
    <div class="card-header bg-white border-bottom py-3">
        <div class="d-flex align-items-center gap-3">
            <div class="rounded-circle bg-light-danger text-danger d-flex align-items-center justify-content-center" style="width:42px;height:42px;">
                <i class="bx bx-palette fs-4"></i>
            </div>
            <div>
                <h5 class="mb-0">Aparência da Loja</h5>
                <small class="text-muted">Logo, tema, cores e imagens exibidas para o cliente.</small>
            </div>
        </div>
    </div>

    <div class="card-body">
        <div class="row g-4">
            <div class="col-lg-4">
                <h6 class="mb-2">Logo da Loja</h6>
                <small class="text-muted d-block mb-3">Use uma imagem nítida, preferencialmente com fundo transparente.</small>
                @if (!isset($not_submit))
                    <div id="image-preview" class="_image-preview" style="max-width:220px;">
                        <label for="image-upload" id="image-label" class="_image-label">Selecionar imagem</label>
                        <input type="file" name="image" id="image-upload" class="_image-upload" accept="image/*" />
                        @isset($item)
                            <img src="{{ $item->img }}" class="img-default">
                        @else
                            <img src="/imgs/no_image.png" class="img-default">
                        @endisset
                    </div>
                @endif
            </div>

            <div class="col-lg-4">
                <h6 class="mb-2">Imagem da Página de Contato</h6>
                <small class="text-muted d-block mb-3">Imagem complementar usada na área de contato.</small>
                @if (!isset($not_submit))
                    <div class="_image-preview" style="max-width:220px;">
                        <label class="_image-label">Selecionar imagem</label>
                        <input type="file" name="img_contato_inp" class="_image-upload" accept="image/*" />
                        @isset($item)
                            <img src="{{ $item->contatoUrl }}" class="img-default-contato">
                        @else
                            <img src="/imgs/no_image.png" class="img-default-contato">
                        @endisset
                    </div>
                @endif
            </div>

            <div class="col-lg-4">
                <h6 class="mb-2">Favicon</h6>
                <small class="text-muted d-block mb-3">Ícone exibido na aba do navegador.</small>
                @if (!isset($not_submit))
                    <div class="_image-preview" style="max-width:220px;">
                        <label class="_image-label">Selecionar favicon</label>
                        <input type="file" name="fav_icon_inp" class="_image-upload" accept="image/*" />
                        @isset($item)
                            <img src="{{ $item->favUrl }}" class="img-default-contato">
                        @else
                            <img src="/imgs/no_image.png" class="img-default-contato">
                        @endisset
                    </div>
                @endif
            </div>
        </div>

        <hr class="my-4">

        <div class="div-tema">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <div>
                    <h6 class="mb-1">Tema da Loja</h6>
                    <small class="text-muted">Escolha o modelo visual utilizado pela vitrine.</small>
                </div>
            </div>

            <div class="row g-3">
                <div class="col-md-6">
                    <div id="template1" onclick="selectTemplate(1)" class="border rounded p-2 h-100 cursor-pointer {{ isset($item) && $item->tema_ecommerce == 'ecommerce' ? 'border-primary bg-light-primary' : '' }}">
                        <img src="/ecommerce/template1.png" class="img-fluid rounded" alt="Tema padrão">
                        <div class="p-2 text-center fw-bold">Tema Padrão</div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div id="template2" onclick="selectTemplate(2)" class="border rounded p-2 h-100 cursor-pointer {{ isset($item) && $item->tema_ecommerce == 'ecommerce_one_tech' ? 'border-primary bg-light-primary' : '' }}">
                        <img src="/ecommerce/template2.png" class="img-fluid rounded" alt="Tema tecnologia">
                        <div class="p-2 text-center fw-bold">Tema Tech</div>
                    </div>
                </div>
            </div>

            <input type="hidden" value="{{ isset($item) ? $item->tema_ecommerce : old('tema_ecommerce', 'ecommerce') }}" name="tema_ecommerce" id="tema_ecommerce">

            <div class="row mt-4 cor" @if(isset($item) && $item->tema_ecommerce == 'ecommerce_one_tech') style="display:none" @endif>
                <div class="col-md-4">
                    <label class="form-label">Cor principal</label>
                    <input name="cor_principal" class="form-control form-control-color" type="color" value="{{ isset($item) && $item->cor_principal ? $item->cor_principal : '#0d6efd' }}">
                </div>
            </div>
        </div>
    </div>
</div>