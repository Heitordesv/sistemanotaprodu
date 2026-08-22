@php
    $decodeEntity = static fn (string $texto): string => html_entity_decode($texto, ENT_QUOTES | ENT_HTML5, 'UTF-8');

    $origens = [
        'Redes Sociais' => ['Instagram', 'Facebook', 'TikTok', 'YouTube', 'LinkedIn', 'Threads', 'X/Twitter', 'Kwai'],
        'Google &amp; Site' => ['Google Pesquisa', 'Google Ads', 'Google Maps', 'Site', 'Landing Page', 'Blog', 'SEO Org&acirc;nico'],
        'Mensagens &amp; Atendimento' => ['WhatsApp', 'Telegram', 'Messenger', 'Chat Online', 'Liga&ccedil;&atilde;o Telef&ocirc;nica'],
        'Marketing' => ['Tr&aacute;fego Pago', 'Org&acirc;nico', 'E-mail Marketing', 'SMS Marketing', 'Remarketing', 'Campanha Promocional'],
        'Indica&ccedil;&otilde;es' => ['Indica&ccedil;&atilde;o Cliente', 'Indica&ccedil;&atilde;o Parceiro', 'Indica&ccedil;&atilde;o Funcion&aacute;rio', 'Amigos/Fam&iacute;lia'],
        'Marketplace' => ['Mercado Livre', 'Shopee', 'OLX', 'Amazon'],
        'Outros' => ['Feira/Eventos', 'Prospec&ccedil;&atilde;o Manual', 'Representante Comercial', 'Parceria', 'Outro'],
    ];

    $tiposNegocio = [
        'Tecnologia &amp; Eletr&ocirc;nicos' => [
            'Manuten&ccedil;&atilde;o de Celular' => 'Assist&ecirc;ncia de Celular',
            'Inform&aacute;tica' => 'Inform&aacute;tica / Assist&ecirc;ncia de PC',
            'Loja de Celulares' => 'Loja de Celulares &amp; Acess&oacute;rios',
            'Eletr&ocirc;nicos' => 'Loja de Eletr&ocirc;nicos',
            'Software House' => 'Sistemas / Softwares / Sites',
            'E-commerce' => 'E-commerce / Loja Virtual',
        ],
        'Alimenta&ccedil;&atilde;o' => [
            'Restaurante' => 'Restaurante',
            'Pizzaria' => 'Pizzaria',
            'Hamburgueria' => 'Hamburgueria',
            'Mercadinho' => 'Mercadinho',
            'Padaria' => 'Padaria',
        ],
        'Sa&uacute;de &amp; Beleza' => [
            'Farm&aacute;cia' => 'Farm&aacute;cia',
            'Sal&atilde;o de Beleza' => 'Sal&atilde;o de Beleza',
            'Barbearia' => 'Barbearia',
            'Academia' => 'Academia',
            'Clinica Medica' => 'Cl&iacute;nica M&eacute;dica',
        ],
        'Automotivo' => [
            'Oficina Mec&acirc;nica' => 'Oficina Mec&acirc;nica',
            'Auto Pe&ccedil;as' => 'Auto Pe&ccedil;as',
            'Lava Jato' => 'Lava Jato',
        ],
        'Servi&ccedil;os' => [
            'Contabilidade' => 'Contabilidade',
            'Advocacia' => 'Advocacia',
            'Grafica' => 'Gr&aacute;fica',
            'Pet Shop' => 'Pet Shop',
            'Escola' => 'Escola / Cursos',
        ],
    ];
@endphp

<div class="row g-3">
    <div class="col-md-6">
        <label for="lead_nome_completo" class="form-label fw-semibold">Nome completo <span class="text-danger">*</span></label>
        <input type="text" id="lead_nome_completo" name="nome_completo" value="{{ old('nome_completo') }}" class="form-control @error('nome_completo') is-invalid @enderror" placeholder="Ex.: Jo&atilde;o Silva" maxlength="255" autocomplete="name" required>
        @error('nome_completo')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-6">
        <label for="lead_whatsapp" class="form-label fw-semibold">WhatsApp <span class="text-danger">*</span></label>
        <input type="tel" id="lead_whatsapp" name="whatsapp" value="{{ old('whatsapp') }}" class="form-control whatsapp-mask @error('whatsapp') is-invalid @enderror" placeholder="(00) 00000-0000" maxlength="20" inputmode="tel" autocomplete="tel" required>
        @error('whatsapp')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-6">
        <label for="lead_email" class="form-label fw-semibold">E-mail</label>
        <input type="email" id="lead_email" name="email" value="{{ old('email') }}" class="form-control @error('email') is-invalid @enderror" placeholder="cliente@email.com" maxlength="255" autocomplete="email">
        @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-6">
        <label for="lead_origem" class="form-label fw-semibold">Origem do lead</label>
        <select id="lead_origem" name="origem_lead" class="form-select @error('origem_lead') is-invalid @enderror">
            <option value="">Cadastro manual</option>
            @foreach($origens as $grupo => $opcoes)
                <optgroup label="{!! $grupo !!}">
                    @foreach($opcoes as $opcao)
                        @php $opcaoReal = $decodeEntity($opcao); @endphp
                        <option value="{{ $opcaoReal }}" {{ old('origem_lead') === $opcaoReal ? 'selected' : '' }}>{!! $opcao !!}</option>
                    @endforeach
                </optgroup>
            @endforeach
        </select>
        @error('origem_lead')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-6">
        <label for="lead_tipo_loja" class="form-label fw-semibold">Tipo de neg&oacute;cio</label>
        <select id="lead_tipo_loja" name="tipo_loja" class="form-select @error('tipo_loja') is-invalid @enderror">
            <option value="">Selecione uma categoria</option>
            @foreach($tiposNegocio as $grupo => $opcoes)
                <optgroup label="{!! $grupo !!}">
                    @foreach($opcoes as $valor => $rotulo)
                        @php $valorReal = $decodeEntity($valor); @endphp
                        <option value="{{ $valorReal }}" {{ old('tipo_loja') === $valorReal ? 'selected' : '' }}>{!! $rotulo !!}</option>
                    @endforeach
                </optgroup>
            @endforeach
            <option value="Outros" {{ old('tipo_loja') === 'Outros' ? 'selected' : '' }}>Outros</option>
        </select>
        @error('tipo_loja')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-6">
        <label for="lead_status" class="form-label fw-semibold">Status inicial</label>
        <select id="lead_status" name="status" class="form-select @error('status') is-invalid @enderror">
            @foreach(['Novo', 'Em Contato', 'Qualificado', 'Convertido', 'Descartado'] as $status)
                <option value="{{ $status }}" {{ old('status', 'Novo') === $status ? 'selected' : '' }}>{{ $status }}</option>
            @endforeach
        </select>
        @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
</div>