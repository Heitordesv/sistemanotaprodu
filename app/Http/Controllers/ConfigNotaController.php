<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\NaturezaOperacao;
use App\Models\ConfigNota;
use App\Models\Cidade;
use App\Models\Certificado;
use App\Utils\UploadUtil;
use NFePHP\Common\Certificate;
use Illuminate\Support\Facades\DB;

class ConfigNotaController extends Controller
{
    protected $util;

    public function __construct(UploadUtil $util)
    {
        $this->util = $util;
    }

    public function certificadosFresh()
    {
        $certificados = DB::table('certificados')->get();
        foreach ($certificados as $c) {
            $config = ConfigNota::find($c->empresa_id);
            if ($config) {
                $config->senha = $c->senha;
                $config->arquivo = $c->arquivo;
                $config->save();
            }
        }
    }

    public function removeSenha($id)
    {
        $config = ConfigNota::find($id);
        $config->senha_remover = '';
        $config->save();
        session()->flash("flash_sucesso", "Senha removida!");
        return redirect()->route('configNF.index');
    }

    public function removeLogo()
    {
        $item = ConfigNota::where('empresa_id', request()->empresa_id)
            ->first();
        $item->logo = '';
        $item->save();
        session()->flash("flash_sucesso", "Logo removida!");
        return redirect()->back();
    }

    public function index(Request $request)
    {
        $item = ConfigNota::where('empresa_id', $request->empresa_id)
            ->first();
        $naturezas = NaturezaOperacao::where('empresa_id', request()->empresa_id)
            ->get();
        $tiposPagamento = ConfigNota::tiposPagamento();
        $tiposFrete = ConfigNota::tiposFrete();
        $listaCSTCSOSN = ConfigNota::listaCST();
        $listaCSTPISCOFINS = ConfigNota::listaCST_PIS_COFINS();
        $listaCSTIPI = ConfigNota::listaCST_IPI();
        $config = ConfigNota::where('empresa_id', request()->empresa_id)
            ->first();
        $cUF = ConfigNota::estados();
        $infoCertificado = null;
        if ($item != null && $item->arquivo != null) {
            $infoCertificado = $this->getInfoCertificado($item);
        }
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

            // se estiver em base64
            if (base64_encode(base64_decode($content, true)) === $content) {
                $content = base64_decode($content);
            }

            $infoCertificado = Certificate::readPfx($content, $item->senha);

            $publicKey = $infoCertificado->publicKey;

            return [
                'serial' => $publicKey->serialNumber,
                'inicio' => $publicKey->validFrom->format('d-m-Y H:i'),
                'expiracao' => $publicKey->validTo->format('d-m-Y H:i'),
                'id' => $publicKey->commonName
            ];

        } catch (\Exception $e) {
            return [
                'erro' => $e->getMessage()
            ];
        }
    }
public function store(Request $request)
{
    // ===============================
    // Validação
    // ===============================
    $this->_validate($request);

    try {
        // ===============================
        // Buscar registro existente
        // ===============================
        $item = ConfigNota::where('empresa_id', $request->empresa_id)->first();

        // ===============================
        // Upload da logo
        // ===============================
        $logo = $item->logo ?? null;

        if ($request->hasFile('image')) {
            // Remove logo antiga se existir
            if ($item && $item->logo) {
                $this->util->unlinkImage($item, '/configEmitente');
            }

            $logo = $this->util->uploadImage($request, '/configEmitente');
        }

        // ===============================
        // Preparação dos dados
        // ===============================
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
            'logo' => $logo
        ];

        // ===============================
        // Upload do Certificado A1 (.pfx)
        // ===============================
        if ($request->hasFile('certificado')) {
            $file = $request->file('certificado');
            $conteudo = file_get_contents($file->getRealPath());

            $data['arquivo'] = $conteudo;
            $data['senha'] = $request->senha;

            // Salvar também como arquivo físico (opcional)
            if (env("CERTIFICADO_ARQUIVO") == 1) {
                $cnpj = preg_replace('/[^0-9]/', '', $request->cnpj);
                $extensao = $file->getClientOriginalExtension();
                $fileName = "$cnpj.$extensao";
                $path = public_path('certificados');

                if (!is_dir($path)) {
                    mkdir($path, 0777, true);
                }

                $file->move($path, $fileName);
            }
        }

        // ===============================
        // CREATE ou UPDATE
        // ===============================
        if (!$item) {
            ConfigNota::create($data);
            session()->flash("flash_sucesso", "Emitente cadastrado com sucesso!");
        } else {
            $item->update($data);
            session()->flash("flash_sucesso", "Emitente atualizado com sucesso!");
        }

        // ===============================
        // Atualizar sessão ambiente
        // ===============================
        $user = session('user_logged');
        if ($user) {
            $user['ambiente'] = $request->ambiente == 1 ? 'Produção' : 'Homologação';
            session()->put('user_logged', $user);
        }

    } catch (\Exception $e) {
        session()->flash("flash_erro", "Erro: " . $e->getMessage());
        __saveLogError($e, $request->empresa_id);
    }

    return redirect()->route('configNF.index');
}

// ===============================
// Validação
// ===============================
private function _validate(Request $request)
{
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
        'csc' => 'required',
        'csc_id' => 'required|max:10',
        'cidade_id' => 'required',
    ];

    $messages = array_map(fn($field) => 'Campo Obrigatório', $rules);
    $messages['csc_id.max'] = 'Máximo de 10 caracteres.';

    $this->validate($request, $rules, $messages);
}

    public function verificaSenha(Request $request)
    {
        $config = ConfigNota::where('senha_remover', md5($request->senha))
            ->where('empresa_id', $request->empresa_id)
            ->first();
        if ($config != null) {
            return response()->json("ok", 200);
        } else {
            return response()->json("", 401);
        }
    }


    public function deleteCertificado()
    {
        $item = ConfigNota::where('empresa_id', request()->empresa_id)
            ->first();
        try {
            $item->arquivo = '';
            $item->save();
            session()->flash("flash_sucesso", "Certificado Removido!");
        } catch (\Exception $e) {
        }

        return redirect()->route('configNF.index');
    }
}