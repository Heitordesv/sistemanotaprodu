<?php

namespace App\Http\Controllers;

use App\Models\Filial;
use App\Models\NaturezaOperacao;
use Illuminate\Http\Request;
use App\Utils\UploadUtil;
use NFePHP\Common\Certificate;

class FilialController extends Controller
{
    protected $util;

    public function __construct(UploadUtil $util)
    {
        $this->util = $util;
    }

    public function index()
    {
        $data = Filial::where('empresa_id', request()->empresa_id)
            ->paginate();
        return view('filial.index', compact('data'));
    }

    public function create()
    {
        $naturezas = NaturezaOperacao::where('empresa_id', request()->empresa_id)
            ->get();
        return view('filial.create', compact('naturezas'));
    }

    public function store(Request $request)
    {
        $this->_validate($request);
        try {
            $file_name = '';
            if ($request->hasFile('image')) {
                $file_name = $this->util->uploadImage($request, '/filial');
            }
            $request->merge([
                'complemento' => $request->complemento ?? '',
                'logo' => $file_name,
                'nat_op_padrao' => $request->nat_op_padrao ?? 0,
            ]);
            if ($request->hasFile('certificado')) {
                $file = $request->file('certificado');
                $temp = file_get_contents($file);
                $extensao = $file->getClientOriginalExtension();
                $request->merge([
                    'arquivo' => $temp
                ]);
                $cnpj = preg_replace('/[^0-9]/', '', $request->cnpj);
                $fileName = "$cnpj.$extensao";
                if (!is_dir(public_path('certificados'))) {
                    mkdir(public_path('certificados'), 0777, true);
                }
                if (env("CERTIFICADO_ARQUIVO") == 1) {
                    $file->move(public_path('certificados'), $fileName);
                }
            }
            Filial::create($request->all());
            session()->flash("flash_sucesso", "Cadastrado com sucesso!");
        } catch (\Exception $e) {
            session()->flash("flash_erro", "Algo deu errado:" . $e->getMessage());
        }
        return redirect()->route('filial.index');
    }

    public function edit($id)
    {
        $item = Filial::findOrFail($id);
        $naturezas = NaturezaOperacao::where('empresa_id', request()->empresa_id)->get();
        $infoCertificado = null;
        if ($item != null && $item->arquivo != null) {
            $infoCertificado = $this->getInfoCertificado($item);
        }
        return view('filial.edit', compact('item', 'naturezas', 'infoCertificado'));
    }

    public function update(Request $request, $id)
    {
        $item = Filial::findOrFail($id);
        $this->_validate($request);
        try {
            $file_name = '';
            if ($request->hasFile('image')) {
                $this->util->unlinkImage($item, '/filial');
                $file_name = $this->util->uploadImage($request, '/filial');
            };
            $request->merge([
                'complemento' => $request->complemento ?? '',
                'logo' => $file_name,
                'nat_op_padrao' => $request->nat_op_padrao ?? 0,
            ]);
            if ($request->hasFile('certificado')) {
                $file = $request->file('certificado');
                $temp = file_get_contents($file);
                $extensao = $file->getClientOriginalExtension();
                $request->merge([
                    'arquivo' => $temp
                ]);
                $cnpj = preg_replace('/[^0-9]/', '', $request->cnpj);
                $fileName = "$cnpj.$extensao";
                if (!is_dir(public_path('certificados'))) {
                    mkdir(public_path('certificados'), 0777, true);
                }
                if (env("CERTIFICADO_ARQUIVO") == 1) {
                    $file->move(public_path('certificados'), $fileName);
                }
            }
            $item->fill($request->all())->save();
            session()->flash("flash_sucesso", "Atualizado com sucesso!");
        } catch (\Exception $e) {
            session()->flash("flash_erro", "Algo deu errado:" . $e->getMessage());
        }
        return redirect()->route('filial.index');
    }

    private function getInfoCertificado($item)
    {
        try {
            $infoCertificado = Certificate::readPfx($item->arquivo, $item->senha);
            $publicKey = $infoCertificado->publicKey;
            $inicio =  $publicKey->validFrom->format('Y-m-d H:i:s');
            $expiracao =  $publicKey->validTo->format('Y-m-d H:i:s');
            return [
                'serial' => $publicKey->serialNumber,
                'inicio' => \Carbon\Carbon::parse($inicio)->format('d-m-Y H:i'),
                'expiracao' => \Carbon\Carbon::parse($expiracao)->format('d-m-Y H:i'),
                'id' => $publicKey->commonName
            ];
        } catch (\Exception $e) {
            return [];
        }
    }

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
            'email' => 'required',
            'fone' => 'required',
            'numero_serie_nfe' => 'required',
            'numero_serie_nfce' => 'required',
            'numero_serie_cte' => 'required',
            'numero_serie_mdfe' => 'required',
            'ultimo_numero_nfe' => 'required',
            'ultimo_numero_nfce' => 'required',
            'ultimo_numero_cte' => 'required',
            'ultimo_numero_mdfe' => 'required',
            'csc' => 'required',
            'csc_id' => 'required|max:10',
            'cidade_id' => 'required',
        ];
        $message = [
            'cnpj.required' => 'Campo Obrigatório',
            'razao_social.required' => 'Campo Obrigatório',
            'nome_fantasia.required' => 'Campo Obrigatório',
            'ie.required' => 'Campo Obrigatório',
            'logradouro.required' => 'Campo Obrigatório',
            'numero.required' => 'Campo Obrigatório',
            'email.required' => 'Campo Obrigatório',
            'bairro.required' => 'Campo Obrigatório',
            'cep.required' => 'Campo Obrigatório',
            'fone.required' => 'Campo Obrigatório',
            'numero_serie_nfe.required' => 'Campo Obrigatório',
            'numero_serie_nfce.required' => 'Campo Obrigatório',
            'numero_serie_cte.required' => 'Campo Obrigatório',
            'numero_serie_mdfe.required' => 'Campo Obrigatório',
            'ultimo_numero_nfe.required' => 'Campo Obrigatório',
            'ultimo_numero_nfce.required' => 'Campo Obrigatório',
            'ultimo_numero_cte.required' => 'Campo Obrigatório',
            'ultimo_numero_mdfe.required' => 'Campo Obrigatório',
            'csc.required' => 'Campo Obrigatório',
            'csc_id.max' => 'Máximo de 10 caracteres.',
            'csc_id.required' => 'Campo Obrigatório',
            'cidade_id.required' => 'Campo Obrigatório'
        ];
        $this->validate($request, $rules, $message);
    }

    public function destroy($id)
    {
        $item = Filial::findOrFail($id);
        try{
            $item->delete();
            session()->flash('flash_sucesso', 'Deletado com sucesso!');
        }catch(\Exception $e){
            session()->flash('flash_erro', 'Algo deu errado: ' . $e->getMessage());
        }
        return redirect()->route('filial.index');
    }
}
