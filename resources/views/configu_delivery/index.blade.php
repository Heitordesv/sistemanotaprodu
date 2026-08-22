@extends('default.layout', ['title' => 'Editar Empresa'])

@section('content')
<div class="page-content">
    <div class="card">
        <div class="card-body p-4">
            <div class="container">
                <form method="POST" action="{{ isset($empresa) ? route('configu_delivery.update', $empresa->id_empresa) : route('configu_delivery.save') }}" enctype="multipart/form-data">
                    @csrf
                    @if (isset($empresa))
                        @method('PUT')
                    @endif

                    <div class="form-group">
                        <label for="nome_empresa">Nome do seu negócio:</label>
                        <input class="form-control" required value="{{ old('nome_empresa', $empresa->nome_empresa ?? '') }}" name="nome_empresa" id="nome_empresa" type="text">
                    </div>

                    <div class="form-group">
                        <label for="descricao_empresa">Breve descrição do seu negócio:</label>
                        <input type="text" required maxlength="297" name="descricao_empresa" class="form-control" placeholder="Digite uma descrição..." value="{{ old('descricao_empresa', $empresa->descricao_empresa ?? '') }}" />
                    </div>

                    <div class="row">
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="telefone_empresa">Suporte WhatsApp:</label>
                                <input required type="tel" placeholder="(99) 99999-9999" data-mask="(00) 00000-0000" maxlength="15" id="telefone_empresa" name="telefone_empresa" value="{{ old('telefone_empresa', $empresa->telefone_empresa ?? '') }}" class="form-control">
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="email_empresa">E-mail:</label>
                                <input required type="email" id="email_empresa" value="{{ old('email_empresa', $empresa->email_empresa ?? '') }}" name="email_empresa" class="form-control">
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="layout">Layout</label>
                        <select required class="form-control" name="layout" id="layout">
                            <option value="0" {{ old('layout', $empresa->layout ?? 0) == 0 ? 'selected' : '' }}>Manutenção</option>
                            <option value="1" {{ old('layout', $empresa->layout ?? 0) == 1 ? 'selected' : '' }}>Ativar loja</option>
                            <option value="2" {{ old('layout', $empresa->layout ?? 0) == 2 ? 'selected' : '' }}>Pausa temporária</option>
                        </select>
                    </div>


                  <div class="form-group">
                        <label for="layout">data agendada</label>
                        <select required class="form-control" name="data_agendada" id="data_agendada">
                            <option value="0" {{ old('data_agendada', $empresa->data_agendada ?? 0) == 0 ? 'selected' : '' }}>Desativar data agendada</option>
                            <option value="1" {{ old('data_agendada', $empresa->data_agendada ?? 0) == 1 ? 'selected' : '' }}>Ativar data agendada</option>
                        </select>
                    </div>





                    <div class="form-group">
                        <label for="ativatele">Frete</label>
                        <select required class="form-control" name="ativatele" id="ativatele">
                            <option value="0" {{ old('ativatele', $empresa->ativatele ?? 0) == 0 ? 'selected' : '' }}>ATIVAR COBRANÇA DE TELE</option>
                            <option value="1" {{ old('ativatele', $empresa->ativatele ?? 0) == 1 ? 'selected' : '' }}>DESATIVAR COBRANÇA DE TELE</option>
                        </select>
                    </div>

                    ---

                    <div class="wrapper_indent">
                        <div class="row">
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label for="facebook_status">Facebook Status:</label>
                                    <select required class="form-control" name="facebook_status">
                                        {{-- Using Blade @if for cleaner conditional options --}}
                                        @if(old('facebook_status', $empresa->facebook_status ?? null) == 2)
                                            <option value="2" selected>Mostrar no Site</option>
                                            <option value="1">Não Mostrar no Site</option>
                                        @else
                                            <option value="1" selected>Não Mostrar no Site</option>
                                            <option value="2">Mostrar no Site</option>
                                        @endif
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="facebook_empresa">Facebook URL:</label>
                                    <input type="text" placeholder="https://www.facebook.com/Meu_Perfil" class="form-control" value="{{ old('facebook_empresa', $empresa->facebook_empresa ?? '') }}" name="facebook_empresa" >
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label for="instagram_status">Instagram Status:</label> {{-- Corrected typo: Instgram -> Instagram --}}
                                    <select required class="form-control" name="instagram_status">
                                        @if(old('instagram_status', $empresa->instagram_status ?? null) == 2)
                                            <option value="2" selected>Mostrar no Site</option>
                                            <option value="1">Não Mostrar no Site</option>
                                        @else
                                            <option value="1" selected>Não Mostrar no Site</option>
                                            <option value="2">Mostrar no Site</option>
                                        @endif
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="instagram_empresa">Instagram URL:</label> {{-- Corrected typo: Instgram -> Instagram --}}
                                    <input type="text" placeholder="https://www.instagram.com/Meu_Perfil" class="form-control" value="{{ old('instagram_empresa', $empresa->instagram_empresa ?? '') }}" name="instagram_empresa" >
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label for="twitter_status">Twitter Status:</label>
                                    <select required class="form-control" name="twitter_status">
                                        @if(old('twitter_status', $empresa->twitter_status ?? null) == 2)
                                            <option value="2" selected>Mostrar no Site</option>
                                            <option value="1">Não Mostrar no Site</option>
                                        @else
                                            <option value="1" selected>Não Mostrar no Site</option>
                                            <option value="2">Mostrar no Site</option>
                                        @endif
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="twitter_empresa">Twitter URL:</label>
                                    <input type="text" placeholder="https://twitter.com/Meu_Perfil" class="form-control" value="{{ old('twitter_empresa', $empresa->twitter_empresa ?? '') }}" name="twitter_empresa" >
                                </div>
                            </div>
                        </div>
                    </div>---

                    <hr>
                    <div class="indent_title_in">
                        <i class="icon_images"></i>
                        <h3>Imagens de fundo e de Perfil</h3>
                        <p>
                            Imagens que serão usadas na página inicial do site!
                        </p>
                    </div>

                   <div class="wrapper_indent add_bottom_45">
    {{-- Added 'enctype="multipart/form-data"' to the form tag above to handle file uploads --}}
    <div class="form-group">
        <label>Imagem utilizada como banner de fundo no site:</label>
        <div class="input-file-container">
            <input name="img_header" class="input-file" id="my-file-header" type="file" />
            <label tabindex="0" for="my-file-header" class="input-file-trigger">Enviar Imagem...</label>
        </div>
        <p class="file-return-header"></p>
        <br />
        @if(!empty($empresa->img_header))
            <span style="color:#70bb0f;">VOCÊ JÁ ENVIOU UMA IMAGEM!</span>
            {{-- Display the header image --}}
            <div class="mt-2">
                <img src="{{ asset($empresa->img_header) }}" alt="Imagem de Banner Atual" style="max-width: 100%; height: auto; border: 1px solid #ddd; padding: 5px;">
            </div>
        @endif
    </div>

    <div class="form-group">
        <label>Imagem de perfil, será redimensionada em 240 X 240:</label>
        <div class="input-file-container">
            <input name="img_logo" class="input-file" id="my-file-logo" type="file" />
            <label tabindex="0" for="my-file-logo" class="input-file-trigger">Enviar Imagem...</label>
        </div>
        <p class="file-return-logo"></p>
        <br />
        @if(!empty($empresa->img_logo))
            <span style="color:#70bb0f;">VOCÊ JÁ ENVIOU UMA IMAGEM!</span>
            {{-- Display the logo image --}}
            <div class="mt-2">
                <img src="{{ asset($empresa->img_logo) }}" alt="Imagem de Perfil Atual" style="max-width: 240px; height: auto; border: 1px solid #ddd; padding: 5px;">
            </div>
        @endif
    </div>
</div>

                    <hr />

                    <div class="indent_title_in">
                        <i class="fa fa-lock" aria-hidden="true"></i>
                        <h3>Formas de Pagamento</h3>
                        <p>
                            Insira suas credenciais!
                        </p>
                    </div>

                    <div class="wrapper_indent">
                        <div class="row">
                            <div class="col-sm-12">
                                <div class="form-group">
                                    <label for="type_pay">Tipo de Pagamento:</label>
                                    <select required class="form-control" name="type_pay">
                                        <option value="0" {{ old('type_pay', $empresa->type_pay ?? 0) == 0 ? 'selected' : '' }}>Pagar online ou na entrega</option>
                                        <option value="1" {{ old('type_pay', $empresa->type_pay ?? 0) == 1 ? 'selected' : '' }}>Pagar somente na entrega</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="indent_title_in">
                        <i class="fa fa-motorcycle" aria-hidden="true"></i>
                        <h3>Opções de entrega</h3>
                        <div class="form-group">
                            <div class="icheck-material-green">
                                <input
                                    type="checkbox"
                                    name="confirm_delivery"
                                    value="true"
                                    id="confirm_delivery"
                                    {{ old('confirm_delivery', $empresa->confirm_delivery ?? 'false') == 'true' ? 'checked' : '' }}
                                />
                                <label for="confirm_delivery"><strong>Permitir delivery</strong></label>
                            </div>

                            <div class="icheck-material-green">
                                <input
                                    type="checkbox"
                                    name="confirm_balcao"
                                    value="true"
                                    id="confirm_balcao"
                                    {{ old('confirm_balcao', $empresa->confirm_balcao ?? 'false') == 'true' ? 'checked' : '' }}
                                />
                                <label for="confirm_balcao"><strong>Permitir retirada no balcão</strong></label>
                            </div>

                            <div class="icheck-material-green">
                                <input
                                    type="checkbox"
                                    name="confirm_mesa"
                                    value="true"
                                    id="confirm_mesa"
                                    {{ old('confirm_mesa', $empresa->confirm_mesa ?? 'false') == 'true' ? 'checked' : '' }}
                                />
                                <label for="confirm_mesa"><strong>Permitir pedido na mesa</strong></label>
                            </div>
                        </div>

                        <p>
                            <span style="color: red;">
                                O valor inserido em "Custo padrão de entrega", será universal se não for adicionando nenhum bairro com taxas diferentes.
                            </span>
                        </p>
                    </div>

                    ---

                    <div class="wrapper_indent">
                        <div class="row">
                            <div class="col-md-6 col-sm-6">
                                <div class="form-group">
                                    <label for="qtcach">Quantidade de pedidos para ativar fidelidade:</label>
                                    <input type="text" class="form-control" id="qtcach" name="qtcach" value="{{ old('qtcach', $empresa->qtcach ?? '0') }}" />
                                </div>
                            </div>

                            <div class="col-md-6 col-sm-6">
                                <div class="form-group">
                                    <label for="valorcach">Valor Mínimo do Delivery para ativar fidelidade:</label>
                                    <input type="text" required maxlength="11" onkeypress="return formatar_moeda(this, '.', ',', event);" data-mask="#.##0,00" data-mask-reverse="true" class="form-control" id="valorcach" name="valorcach" value="{{ old('valorcach', isset($empresa->valorcach) ? number_format($empresa->valorcach, 2, ',', '.') : '0,00') }}" />
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12 col-sm-12">
                                <div class="form-group">
                                    <label for="cupom">Cupom:</label>
                                    <input type="text" class="form-control" id="cupom" name="cupom" value="{{ old('cupom', $empresa->cupom ?? 'cupom') }}" />
                                </div>
                            </div>
                        </div>
<div class="row">
    <div class="col-sm-6">
        <div class="form-group">
            <label for="cor_topo">COR DO TOPO:</label>
            <input type="color" required id="cor_topo" name="cor_topo"
                   value="{{ old('cor_topo', $empresa->cor_topo ?? '#ffffff') }}" class="form-control">
        </div>
    </div>

    <div class="col-sm-6">
        <div class="form-group">
            <label for="cor_titulo_produtos">COR TÍTULO TOPO PRODUTOS:</label>
            <input type="color" required id="cor_titulo_produtos" name="cor_titulo_produtos"
                   value="{{ old('cor_titulo_produtos', $empresa->cor_titulo_produtos ?? '#ffffff') }}" class="form-control">
        </div>
    </div>
</div>

<div class="row">
    <div class="col-sm-6">
        <div class="form-group">
            <label for="cor_loading">COR LOADING:</label>
            <input type="color" required id="cor_loading" name="cor_loading"
                   value="{{ old('cor_loading', $empresa->cor_loading ?? '#ffffff') }}" class="form-control">
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6 col-sm-6">
        <div class="form-group">
            <label for="config_delivery">Custo padrão de entrega:</label>
            <input type="text" required maxlength="11" onkeypress="return formatar_moeda(this, '.', ',', event);" data-mask="#.##0,00" data-mask-reverse="true" class="form-control" id="config_delivery" name="config_delivery" value="{{ old('config_delivery', isset($empresa->config_delivery) ? number_format($empresa->config_delivery, 2, ',', '.') : '0,00') }}" />
        </div>
    </div>

    <div class="col-md-6 col-sm-6">
        <div class="form-group">
            <label for="minimo_delivery">Valor Mínimo do Delivery:</label>
            <input type="text" required maxlength="11" onkeypress="return formatar_moeda(this, '.', ',', event);" data-mask="#.##0,00" data-mask-reverse="true" class="form-control" id="minimo_delivery" name="minimo_delivery" value="{{ old('minimo_delivery', isset($empresa->minimo_delivery) ? number_format($empresa->minimo_delivery, 2, ',', '.') : '0,00') }}" />
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6 col-sm-6">
        <div class="form-group">
            <label>Mensagem sobre tempo de Delivery:</label>
            <input type="text" required class="form-control" id="msg_tempo_delivery" name="msg_tempo_delivery" value="{{ old('msg_tempo_delivery', $empresa->msg_tempo_delivery ?? 'Entre 30 e 60 minutos.') }}" />
        </div>
    </div>
    <div class="col-md-6 col-sm-6">
        <div class="form-group">
            <label>Mensagem sobre retirar no local:</label>
            <input type="text" required class="form-control" id="msg_tempo_buscar" name="msg_tempo_buscar" value="{{ old('msg_tempo_buscar', $empresa->msg_tempo_buscar ?? 'Em 30 minutos.') }}" />
        </div>
    </div>
</div>
                        </div>
                    </div>
                    <br>
                    <button type="submit" class="btn btn-primary">Salvar</button>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- NOTA: O script JS para checkboxes é bom para garantir o envio, mas os mutators no MODELO WsConfiempresa
     são os que realmente formatarão para 'true'/'false' (string) no banco de dados. --}}
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const form = document.querySelector('form');

        if (!form) return;

        form.addEventListener('submit', function () {
            // Checkboxes de "confirm_delivery", "confirm_balcao", "confirm_mesa"
            const confirmationCheckboxes = ['confirm_delivery', 'confirm_balcao', 'confirm_mesa'];

            confirmationCheckboxes.forEach(function (name) {
                const checkbox = document.getElementById(name);

                // Remove input hidden duplicado antes de criar um novo
                const existingHidden = document.querySelector(`input[type="hidden"][name="${name}"]`);
                if (existingHidden) existingHidden.remove();

                if (!checkbox.checked) {
                    const hidden = document.createElement('input');
                    hidden.type = 'hidden';
                    hidden.name = name;
                    hidden.value = 'false'; // Garante que 'false' (string) seja enviado se desmarcado
                    checkbox.parentNode.appendChild(hidden);
                }
            });

            // Campos de status de redes sociais (Facebook, Instagram, Twitter)
            // Estes são <select> e não checkboxes, então o tratamento é diferente.
            // O valor '1' ou '2' já será enviado pelo <select>
        });

        // Script para exibir o nome do arquivo selecionado (para img_header e img_logo)
        ['my-file-header', 'my-file-logo'].forEach(function(id) {
            const fileInput = document.getElementById(id);
            if (fileInput) {
                fileInput.addEventListener('change', function(e) {
                    const fileName = e.target.files[0] ? e.target.files[0].name : '';
                    const returnElementClass = `file-return-${id.split('-')[2]}`; // e.g., 'file-return-header'
                    const fileReturn = document.querySelector(`.${returnElementClass}`);
                    if (fileReturn) {
                        fileReturn.textContent = fileName;
                    }
                });
            }
        });
    });
</script>
@endsection