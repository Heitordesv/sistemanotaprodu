<?php

namespace App\Http\Controllers;

use App\Models\ConfigEcommerce;
use Illuminate\Http\Request;
use App\Utils\UploadUtil;
use Illuminate\Support\Facades\DB;

class ConfigEcommerceController extends Controller
{
    protected $util;
    protected $empresa_id = null;

    public function __construct(UploadUtil $util)
    {
        $this->util = $util;
        $this->middleware(function ($request, $next) {
            $this->empresa_id = $request->empresa_id;
            $value = session('user_logged');
            if (!$value) {
                return redirect('/login');
            }
            return $next($request);
        });
    }

    public function index(Request $request)
    {
        $item = ConfigEcommerce::where('empresa_id', $request->empresa_id)->first();
        return view('config_ecommerce.create', compact('item'));
    }

    public function store(Request $request)
    {
        $this->_validate($request);
        $item = ConfigEcommerce::where('empresa_id', $request->empresa_id)->first();

        if (!__valida_objeto($item)) {
            abort(403);
        }

        try {
            if ($item == null) {
                $file_name = '';
                $file_contato = '';
                $file_icon = '';

                if ($request->hasFile('image')) {
                    $file_name = $this->util->uploadImage($request, '/configEcommerce');
                }

                if ($request->hasFile('img_contato_inp')) {
                    $file_contato = $this->util->uploadImage($request, '/contatoEcommerce', 'img_contato_inp');
                }

                if ($request->hasFile('fav_icon_inp')) {
                    $file_icon = $this->util->uploadImage($request, '/favIcon', 'fav_icon_inp');
                }

                $request->merge([
                    'imagem' => $file_name,
                    'token' => $request->token ?? '',
                    'link_facebook' => $request->link_facebook ?? '',
                    'link_instagram' => $request->link_instagram ?? '',
                    'link_twiter' => $request->link_twiter ?? '',
                    'frete_gratis_valor' => $request->frete_gratis_valor ? __convert_value_bd($request->frete_gratis_valor) : 0,
                    'mercadopago_public_key' => trim((string) ($request->mercadopago_public_key ?? '')),
                    'mercadopago_access_token' => trim((string) ($request->mercadopago_access_token ?? '')),
                    'google_api' => $request->google_api ?? '',
                    'habilitar_retirada' => $request->habilitar_retirada ?? 0,
                    'desconto_padrao_boleto' => $request->desconto_padrao_boleto ?? 0,
                    'desconto_padrao_pix' => $request->desconto_padrao_pix ?? 0,
                    'desconto_padrao_cartao' => $request->desconto_padrao_cartao ?? 0,
                    'politica_privacidade' => $request->politica_privacidade ?? '',
                    'src_mapa' => $request->src_mapa ?? '',
                    'usar_api' => $request->usar_api ? true : false,
                    'api_token' => $request->api_token ?? '',
                    'mensagem_agradecimento' => $request->mensagem_agradecimento ?? '',
                    'cor_fundo' => $request->cor_fundo ?? '#000',
                    'cor_btn' => $request->cor_btn ?? '#000',
                    'timer_carrossel' => $request->timer_carrossel ?? 5,
                    'img_contato' => $file_contato,
                    'fav_icon' => $file_icon,
                    'tema_ecommerce' => $request->tema_ecommerce ?? 'ecommerce',
                    'cor_principal' => $request->cor_principal ?? '',
                    'formas_pagamento' => json_encode($request->formas_pagamento),
                    'correios_usuario' => trim((string) ($request->correios_usuario ?? '')),
                    'correios_senha' => trim((string) ($request->correios_senha ?? '')),
                    'correios_cartao' => preg_replace('/\D/', '', (string) ($request->correios_cartao ?? '')),
                ]);

                DB::transaction(function () use ($request) {
                    ConfigEcommerce::create($request->all());
                    session()->flash('flash_sucesso', 'Cadastrado com sucesso');
                });
            } else {
                $file_name = $item->imagem;
                $file_contato = $item->img_contato;
                $file_icon = $item->fav_icon;

                if ($request->hasFile('image')) {
                    $this->util->unlinkImage($item, '/configEcommerce');
                    $file_name = $this->util->uploadImage($request, '/configEcommerce');
                }

                if ($request->hasFile('img_contato_inp')) {
                    $this->util->unlinkImage($item, '/contatoEcommerce', 'img_contato_inp');
                    $file_contato = $this->util->uploadImage($request, '/contatoEcommerce', 'img_contato_inp');
                }

                if ($request->hasFile('fav_icon_inp')) {
                    $this->util->unlinkImage($item, '/favIcon', 'fav_icon_inp');
                    $file_icon = $this->util->uploadImage($request, '/favIcon', 'fav_icon_inp');
                }

                $dados = $request->all();
                $dados['imagem'] = $file_name;
                $dados['token'] = $request->token ?? '';
                $dados['link_facebook'] = $request->link_facebook ?? '';
                $dados['link_instagram'] = $request->link_instagram ?? '';
                $dados['link_twiter'] = $request->link_twiter ?? '';
                $dados['frete_gratis_valor'] = $request->frete_gratis_valor ? __convert_value_bd($request->frete_gratis_valor) : 0;
                $dados['google_api'] = $request->google_api ?? '';
                $dados['habilitar_retirada'] = $request->habilitar_retirada ?? 0;
                $dados['desconto_padrao_boleto'] = $request->desconto_padrao_boleto ?? 0;
                $dados['desconto_padrao_pix'] = $request->desconto_padrao_pix ?? 0;
                $dados['desconto_padrao_cartao'] = $request->desconto_padrao_cartao ?? 0;
                $dados['politica_privacidade'] = $request->politica_privacidade ?? '';
                $dados['src_mapa'] = $request->src_mapa ?? '';
                $dados['usar_api'] = $request->usar_api ?? '';
                $dados['api_token'] = $request->api_token ?? '';
                $dados['mensagem_agradecimento'] = $request->mensagem_agradecimento ?? '';
                $dados['cor_fundo'] = $request->cor_fundo ?? '';
                $dados['cor_btn'] = $request->cor_btn ?? '';
                $dados['timer_carrossel'] = $request->timer_carrossel ?? 5;
                $dados['img_contato'] = $file_contato;
                $dados['fav_icon'] = $file_icon;
                $dados['tema_ecommerce'] = $request->tema_ecommerce ?? '';
                $dados['cor_principal'] = $request->cor_principal ?? '';
                $dados['formas_pagamento'] = json_encode($request->formas_pagamento);

                // Credenciais sensíveis só são substituídas quando um novo valor for informado.
                // Salvar aparência, pagamento ou qualquer outra configuração não pode apagar Correios.
                if ($request->filled('correios_usuario')) {
                    $dados['correios_usuario'] = trim((string) $request->correios_usuario);
                } else {
                    unset($dados['correios_usuario']);
                }

                if ($request->filled('correios_senha')) {
                    $dados['correios_senha'] = trim((string) $request->correios_senha);
                } else {
                    unset($dados['correios_senha']);
                }

                if ($request->filled('correios_cartao')) {
                    $dados['correios_cartao'] = preg_replace('/\D/', '', (string) $request->correios_cartao);
                } else {
                    unset($dados['correios_cartao']);
                }

                // Mercado Pago também só muda quando uma nova credencial é informada.
                if ($request->filled('mercadopago_public_key')) {
                    $dados['mercadopago_public_key'] = trim((string) $request->mercadopago_public_key);
                } else {
                    unset($dados['mercadopago_public_key']);
                }

                if ($request->filled('mercadopago_access_token')) {
                    $dados['mercadopago_access_token'] = trim((string) $request->mercadopago_access_token);
                } else {
                    unset($dados['mercadopago_access_token']);
                }

                if ($request->filled('mercadopago_webhook_secret')) {
                    $dados['mercadopago_webhook_secret'] = trim((string) $request->mercadopago_webhook_secret);
                } else {
                    unset($dados['mercadopago_webhook_secret']);
                }

                $item->fill($dados)->save();
                session()->flash('flash_sucesso', 'Configurações atualizadas!');
            }
        } catch (\Exception $e) {
            session()->flash('flash_erro', 'Algo deu errado: ' . $e->getMessage());
            __saveLogError($e, request()->empresa_id);
        }

        return redirect()->route('configEcommerce.index');
    }

    private function _validate(Request $request)
    {
        $rules = [
            'nome' => 'required',
            'link' => 'required',
            'rua' => 'required',
            'numero' => 'required',
            'bairro' => 'required',
            'cidade_id' => 'required',
            'cep' => 'required',
            'email' => 'required',
            'telefone' => 'required',
            'latitude' => 'required|max:13',
            'longitude' => 'required|max:13',
            'funcionamento' => 'required',
            'uf' => 'required',
        ];

        $messages = [
            'nome.required' => 'Campo obrigatório',
            'link.required' => 'Campo obrigatório',
            'rua.required' => 'Campo obrigatório',
            'numero.required' => 'Campo obrigatório',
            'bairro.required' => 'Campo obrigatório',
            'cidade_id.required' => 'Campo obrigatório',
            'cep.required' => 'Campo obrigatório',
            'email.required' => 'Campo obrigatório',
            'telefone.required' => 'Campo obrigatório',
            'latitude.required' => 'Campo obrigatório',
            'longitude.required' => 'Campo obrigatório',
            'latitude.max' => 'Máximo 13 caracteres',
            'longitude.max' => 'Máximo 13 caracteres',
            'funcionamento.required' => 'Campo obrigatório',
            'uf.required' => 'Campo obrigatório',
        ];

        $this->validate($request, $rules, $messages);
    }

    public function verSite()
    {
        $config = ConfigEcommerce::where('empresa_id', $this->empresa_id)->first();
        if ($config == null) {
            session()->flash('flash_erro', 'Configure o ecommerce!');
            return redirect('/configEcommerce');
        }
        return redirect('/loja/' . strtolower($config->link));
    }
}