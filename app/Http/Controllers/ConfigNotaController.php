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
        $empresaId = (int) $request->empresa_id;
        $certificado = $this->certificates->forEmpresa($empresaId);
        $config = ConfigNota::where('empresa_id', $empresaId)->first();

        if ($certificado && $config) {
            $config->senha = $certificado->senha;
            $config->arquivo = $certificado->arquivo;
            $config->save();
        }
    }

    public function removeSenha($id)
    {
        $config = ConfigNota::where('id', $id)
            ->where('empresa_id', request()->empresa_id)
            ->firstOrFail();

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
                'pais' => $request->pais ?? '',
                'cUF' => $request->cUF ?? '',
                'fone' => $request->fone ?? '',
                'email' => $request->email ?? '',
                'campo_obs_pedido' => $request->campo_obs_pedido ?? '',
                'campo_obs_nfe' => $request->campo_obs_nfe ?? '',
                'certificado_a3' => $request->certificado_a3 ?? 0,
                'inscricao_municipal' => $request->inscricao_municipal ?? '',
                'token_ibpt' => $request->token_ibpt ?? '',
                'percentual_lucro_padrao' => $request->percentual_lucro_padrao ?? 0,
                'validade_orcamento' => $request->validade_orcamento ?? 0,
                'casas_decimais' => $request->casas_decimais ?? 2,
                'nat_op_padrao' => $request->nat_op_padrao ?? '',
                'parcelamento_maximo' => $request->parcelamento_maximo ?? 12,
                'sobrescrita_csonn_consumidor_final' => $request->sobrescrita_csonn_consumidor_final ?? '',
                'senha_remover' => $request->senha_remover ? md5($request->senha_remover) : ($item->senha_remover ?? ''),
                'background_color' => $request->background_color ?? ($item->background_color ?? '#343a40'),
                'text_color' => $request->text_color ?? ($item->text_color ?? '#ffffff'),
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
        $config->setAttribute('csc', null);
        $config->setAttribute('token_ibpt', null);
        $config->setAttribute('token_nfse', null);
        $config->setAttribute('DeviceToken', null);
        $config->setAttribute('Bearer', null);
    }
}
