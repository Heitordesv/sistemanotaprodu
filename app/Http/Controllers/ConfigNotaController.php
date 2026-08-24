<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\NaturezaOperacao;
use App\Models\ConfigNota;
use App\Models\Cidade;
use App\Utils\UploadUtil;
use App\Services\FiscalCertificateService;
use NFePHP\Common\Certificate;

class ConfigNotaController extends Controller
{
    protected $util;

    public function __construct(UploadUtil $util, private FiscalCertificateService $certificates)
    {
        $this->util = $util;
    }

    public function certificadosFresh(Request $request)
    {
        if (!$request->isMethod('post')) {
            return response()->json(['message' => 'Método não permitido.'], 405);
        }

        $empresaId = (int) $request->empresa_id;
        $certificado = $this->certificates->forEmpresa($empresaId);
        $config = ConfigNota::where('empresa_id', $empresaId)->first();

        if ($certificado && $config) {
            $config->senha = $certificado->senha;
            $config->arquivo = $certificado->arquivo;
            $config->save();
        }

        return redirect()->route('configNF.index');
    }

    public function removeSenha($id)
    {
        $config = ConfigNota::where('id', $id)
            ->where('empresa_id', request()->empresa_id)
            ->firstOrFail();

        if (!request()->isMethod('post')) {
            return view('config_nota.confirm_remove_password', [
                'configId' => $config->id,
            ]);
        }

        $config->senha_remover = '';
        $config->save();

        session()->flash('flash_sucesso', 'Senha removida!');
        return redirect()->route('configNF.index');
    }

    public function removeLogo()
    {
        $item = ConfigNota::where('empresa_id', request()->empresa_id)->firstOrFail();
        $item->logo = '';
        $item->save();

        session()->flash('flash_sucesso', 'Logo removida!');
        return redirect()->back();
    }

    public function index(Request $request)
    {
        $item = ConfigNota::where('empresa_id', $request->empresa_id)->first();
        $naturezas = NaturezaOperacao::where('empresa_id', request()->empresa_id)->get();
        $tiposPagamento = ConfigNota::tiposPagamento();
        $tiposFrete = ConfigNota::tiposFrete();
        $listaCSTCSOSN = ConfigNota::listaCST();
        $listaCSTPISCOFINS = ConfigNota::listaCST_PIS_COFINS();
        $listaCSTIPI = ConfigNota::listaCST_IPI();
        $config = ConfigNota::where('empresa_id', request()->empresa_id)->first();
        $cUF = ConfigNota::estados();
        $infoCertificado = null;

        if ($item != null && $item->arquivo != null) {
            $infoCertificado = $this->getInfoCertificado($item);
        }

        $cscCadastrado = $item !== null && !empty($item->getRawOriginal('csc'));

        $this->maskSecretsForForm($item);
        $this->maskSecretsForForm($config);

        $soapDesativado = !extension_loaded('soap');
        $cidades = Cidade::all();

        return view(
            'config_nota/index',
            compact(
                'config',
                'naturezas',
                'tiposPagamento',
                'tiposFrete',
                'infoCertificado',
                'soapDesativado',
                'listaCSTCSOSN',
                'listaCSTPISCOFINS',
                'listaCSTIPI',
                'cUF',
                'cidades',
                'cscCadastrado',
                'item'
            )
        );
    }

    private function getInfoCertificado($item)
    {
        try {
            $content = $item->arquivo;

            if (base64_encode(base64_decode($content, true)) === $content) {
                $content = base64_decode($content);
            }

            $infoCertificado = Certificate::readPfx($content, $item->senha);
            $publicKey = $infoCertificado->publicKey;

            return [
                'serial' => $publicKey->serialNumber,
                'inicio' => $publicKey->validFrom->format('d-m-Y H:i'),
                'expiracao' => $publicKey->validTo->format('d-m-Y H:i'),
                'id' => $publicKey->commonName,
            ];
        } catch (\Throwable $e) {
            report($e);

            return [
                'erro' => 'Não foi possível ler o certificado digital.',
            ];
        }
    }

    public function store(Request $request)
    {
        $this->_validate($request);

        try {
            $item = ConfigNota::where('empresa_id', $request->empresa_id)->first();
            $logo = $item->logo ?? null;
            $cidade = Cidade::findOrFail($request->cidade_id);

            if ($request->hasFile('image')) {
                if ($item && $item->logo) {
                    $this->util->unlinkImage($item, '/configEmitente');
                }

                $logo = $this->util->uploadImage($request, '/configEmitente');
            }

            $data = [
                'empresa_id' => $request->empresa_id,
                'razao_social' => $request->razao_social ?? '',
                'nome_fantasia' => $request->nome_fantasia ?? '',
                'cnpj' => $request->cnpj ?? '',
                'ie' => $request->ie ?? '',
                'logradouro' => $request->logradouro ?? '',
                'numero' => $request->numero ?? '',
                'bairro' => $request->bairro ?? '',
                'cep' => $request->cep ?? '',
                'complemento' => $request->complemento ?? '',
                'cidade_id' => $request->cidade_id ?? '',
                'pais' => $request->pais ?? ($item->pais ?? 'Brasil'),
                'cUF' => $request->cUF ?? ($item->cUF ?? ConfigNota::getCodUF($cidade->uf)),
                'fone' => $request->fone ?? '',
                'email' => $request->email ?? '',
                'CST_CSOSN_padrao' => $request->CST_CSOSN_padrao ?? ($item->CST_CSOSN_padrao ?? '102'),
                'CST_COFINS_padrao' => $request->CST_COFINS_padrao ?? ($item->CST_COFINS_padrao ?? '01'),
                'CST_PIS_padrao' => $request->CST_PIS_padrao ?? ($item->CST_PIS_padrao ?? '01'),
                'CST_IPI_padrao' => $request->CST_IPI_padrao ?? ($item->CST_IPI_padrao ?? '50'),
                'frete_padrao' => $request->frete_padrao ?? ($item->frete_padrao ?? 9),
                'tipo_pagamento_padrao' => $request->tipo_pagamento_padrao ?? ($item->tipo_pagamento_padrao ?? '01'),
                'ambiente' => (int) $request->ambiente,
                'numero_serie_nfe' => $request->numero_serie_nfe,
                'numero_serie_nfce' => $request->numero_serie_nfce,
                'numero_serie_cte' => $request->numero_serie_cte,
                'numero_serie_mdfe' => $item->numero_serie_mdfe ?? '1',
                'ultimo_numero_nfe' => (int) $request->ultimo_numero_nfe,
                'ultimo_numero_nfce' => (int) $request->ultimo_numero_nfce,
                'ultimo_numero_cte' => (int) $request->ultimo_numero_cte,
                'ultimo_numero_mdfe' => (int) ($item->ultimo_numero_mdfe ?? 0),
                'campo_obs_pedido' => $request->campo_obs_pedido ?? ($item->campo_obs_pedido ?? ''),
                'campo_obs_nfe' => $request->observacao_nfe ?? $request->campo_obs_nfe ?? ($item->campo_obs_nfe ?? ''),
                'certificado_a3' => $request->certificado_a3 ?? 0,
                'inscricao_municipal' => $request->inscricao_municipal ?? '',
                'aut_xml' => $request->auto_xml ?? $request->aut_xml ?? ($item->aut_xml ?? ''),
                'token_ibpt' => $request->filled('token_ibpt') ? $request->token_ibpt : ($item->token_ibpt ?? ''),
                'token_nfse' => $request->filled('token_nfse') ? $request->token_nfse : ($item->token_nfse ?? ''),
                'percentual_lucro_padrao' => $request->percentual_lucro_padrao ?? 0,
                'percentual_max_desconto' => $request->parcentual_max_desconto ?? $request->percentual_max_desconto ?? ($item->percentual_max_desconto ?? 0),
                'validade_orcamento' => $request->validade_orcamento ?? 0,
                'casas_decimais' => $request->casas_decimais ?? 2,
                'nat_op_padrao' => $request->nat_op_padrao ?? '',
                'parcelamento_maximo' => $request->parcelamento_maximo ?? 12,
                'sobrescrita_csonn_consumidor_final' => $request->sobrescrita_csonn_consumidor_final ?? '',
                'caixa_por_usuario' => $request->caixa_por_usuario ?? ($item->caixa_por_usuario ?? 0),
                'codigo_tributacao_municipio' => $request->codigo_tributacao_municipio ?? ($item->codigo_tributacao_municipio ?? ''),
                'usar_email_proprio' => $request->usar_email_proprio ?? ($item->usar_email_proprio ?? 0),
                'cProdTipo' => $request->cProdTipo ?? ($item->cProdTipo ?? 0),
                'graficos_dash' => $item->graficos_dash ?? '[]',
                'senha_remover' => $request->senha_remover ? md5($request->senha_remover) : ($item->senha_remover ?? ''),
                'background_color' => $request->background_color ?? ($item->background_color ?? '#343a40'),
                'text_color' => $request->text_color ?? ($item->text_color ?? '#ffffff'),
                'csc' => ($request->filled('csc') && $request->csc !== '********') ? $request->csc : ($item->csc ?? ''),
                'csc_id' => $request->csc_id ?? ($item->csc_id ?? ''),
                'logo' => $logo,
            ];

            if ($request->hasFile('certificado')) {
                $file = $request->file('certificado');
                $conteudo = file_get_contents($file->getRealPath());

                $data['arquivo'] = $conteudo;
                $data['senha'] = $request->senha;

                if (env('CERTIFICADO_ARQUIVO') == 1) {
                    $cnpj = preg_replace('/[^0-9]/', '', $request->cnpj);
                    $extensao = $file->getClientOriginalExtension();
                    $fileName = $cnpj . '.' . $extensao;
                    $path = storage_path('app/private/certificados/' . (int) $request->empresa_id);

                    if (!is_dir($path)) {
                        mkdir($path, 0700, true);
                    }

                    file_put_contents($path . DIRECTORY_SEPARATOR . $fileName, $conteudo, LOCK_EX);
                    @chmod($path . DIRECTORY_SEPARATOR . $fileName, 0600);
                }
            }

            if (!$item) {
                ConfigNota::create($data);
                session()->flash('flash_sucesso', 'Emitente cadastrado com sucesso!');
            } else {
                $item->update($data);
                session()->flash('flash_sucesso', 'Emitente atualizado com sucesso!');
            }

            $user = session('user_logged');
            if ($user) {
                $user['ambiente'] = $request->ambiente == 1 ? 'Produção' : 'Homologação';
                session()->put('user_logged', $user);
            }
        } catch (\Throwable $e) {
            session()->flash('flash_erro', 'Não foi possível atualizar a configuração fiscal.');
            __saveLogError($e, $request->empresa_id);
        }

        return redirect()->route('configNF.index');
    }

    private function _validate(Request $request)
    {
        $config = ConfigNota::where('empresa_id', $request->empresa_id)->first();
        $cscRule = $config && !empty($config->csc) ? 'nullable' : 'required';

        $rules = [
            'cnpj' => 'required',
            'razao_social' => 'required',
            'nome_fantasia' => 'required',
            'ie' => 'required',
            'logradouro' => 'required',
            'numero' => 'required',
            'bairro' => 'required',
            'cep' => 'required',
            'email' => 'required|email',
            'fone' => 'required',
            'numero_serie_nfe' => 'required',
            'numero_serie_nfce' => 'required',
            'numero_serie_cte' => 'required',
            'ultimo_numero_nfe' => 'required',
            'ultimo_numero_nfce' => 'required',
            'ultimo_numero_cte' => 'required',
            'csc' => $cscRule,
            'csc_id' => 'required|max:10',
            'cidade_id' => 'required',
        ];

        $messages = array_map(fn ($field) => 'Campo Obrigatório', $rules);
        $messages['csc_id.max'] = 'Máximo de 10 caracteres.';

        $this->validate($request, $rules, $messages);
    }

    public function verificaSenha(Request $request)
    {
        $config = ConfigNota::where('senha_remover', md5($request->senha))
            ->where('empresa_id', $request->empresa_id)
            ->first();

        return $config != null
            ? response()->json('ok', 200)
            : response()->json('', 401);
    }

    public function deleteCertificado()
    {
        $empresaId = (int) request()->empresa_id;
        $item = ConfigNota::where('empresa_id', $empresaId)->firstOrFail();

        if (!request()->isMethod('post')) {
            return view('config_nota.confirm_delete_certificate');
        }

        try {
            $item->arquivo = '';
            $item->senha = '';
            $item->save();
            $this->certificates->deleteForEmpresa($empresaId);

            session()->flash('flash_sucesso', 'Certificado removido!');
        } catch (\Throwable $e) {
            report($e);
            session()->flash('flash_erro', 'Não foi possível remover o certificado digital.');
        }

        return redirect()->route('configNF.index');
    }

    private function maskSecretsForForm(?ConfigNota $config): void
    {
        if (!$config) {
            return;
        }

        $config->setAttribute('arquivo', null);
        $config->setAttribute('senha', null);
        $config->setAttribute('senha_remover', null);
        $config->setAttribute('csc', !empty($config->getRawOriginal('csc')) ? '********' : null);
        $config->setAttribute('token_ibpt', null);
        $config->setAttribute('token_nfse', null);
        $config->setAttribute('DeviceToken', null);
        $config->setAttribute('Bearer', null);
    }
}
