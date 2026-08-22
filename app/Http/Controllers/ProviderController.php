<?php

namespace App\Http\Controllers;

use App\Http\Middleware\LimiteFornecedor;
use Illuminate\Http\Request;
use App\Models\Fornecedor;
use App\Models\Cidade;
use App\Rules\ValidaDocumento;
use Illuminate\Support\Facades\DB;
use Dompdf\Dompdf;

class ProviderController extends Controller
{
    public function __construct()
    {
        $this->middleware(LimiteFornecedor::class)->only('create');
    }

    public function index(Request $request)
    {
        $data = Fornecedor::where('empresa_id', request()->empresa_id)
            ->when(!empty($request->razao_social), function ($q) use ($request) {
                return  $q->where(function ($quer) use ($request) {
                    return $quer->where('razao_social', 'LIKE', "%$request->razao_social%");
                });
            })
            ->when(!empty($request->cpf_cnpj), function ($q) use ($request) {
                return  $q->where(function ($quer) use ($request) {
                    return $quer->where('cpf_cnpj', 'LIKE', "%$request->cpf_cnpj%");
                });
            })
            ->paginate(env("PAGINACAO"));
        return view('fornecedores.index', compact('data'));
    }

    public function create()
    {
        return view('fornecedores/create');
    }


    public function store(Request $request)
    {
        $this->_validate($request);
        try {
            $request->merge([
                'ie_rg' => $request->ie_rg ?? '',
                'email' => $request->email ?? '',
                'telefone' => $request->telefone ?? ''
            ]);
            DB::transaction(function () use ($request) {
                Fornecedor::create($request->all());
            });
            session()->flash("flash_sucesso", "Fornecedor cadastrado!");
        } catch (\Exception $e) {
            session()->flash("flash_erro", "Algo deu errado: " . $e->getMessage());
            __saveLogError($e, request()->empresa_id);
        }
        return redirect()->route('fornecedores.index');
    }

public function canhotos($id)
{
    $empresa = Fornecedor::findOrFail($id);

    // Renderiza a view como HTML
    $html = view('fornecedores.canhotos', compact('empresa'))->render();

    // Instancia o Dompdf
    $dompdf = new Dompdf();

    // Carrega o HTML
    $dompdf->loadHtml($html);

    // Define papel
    $dompdf->setPaper('A4', 'portrait');

    // Renderiza
    $dompdf->render();

    // Baixar PDF
    return response()->streamDownload(
        fn () => print($dompdf->output()),
        'canhotos-'.$empresa->razao_social.'.pdf'
    );
}
    public function update(Request $request, $id)
    {
        $this->_validate($request);
        $item = Fornecedor::findOrFail($id);
        try {
            $request->merge([
                'ie_rg' => $request->ie_rg ?? '',
                'email' => $request->email ?? '',
                'telefone' => $request->telefone ?? ''
            ]);
            DB::transaction(function () use ($request, $item) {
                $item->fill($request->all())->save();
            });
            session()->flash("flash_sucesso", "Fornecedor atualizado!");
        } catch (\Exception $e) {
            session()->flash("flash_erro", "Algo deu errado: " . $e->getMessage());
            __saveLogError($e, request()->empresa_id);
        }
        return redirect()->route('fornecedores.index');
    }

    public function edit($id)
    {
        $item = Fornecedor::findOrFail($id);
        if (!__valida_objeto($item)) {
            abort(403);
        }
        return view('fornecedores.edit', compact('item'));
    }

    private function _validate(Request $request)
    {
        $doc = $request->cpf_cnpj;
        $rules = [
            'razao_social' => 'required|max:80',
            'nome_fantasia' => strlen($doc) > 14 ? 'required|max:80' : 'max:80',
            'cpf_cnpj' => ['required', new ValidaDocumento],
            'rua' => 'required|max:80',
            'numero' => 'required|max:10',
            'bairro' => 'required|max:50',
            'telefone' => 'max:20',
            'celular' => 'max:20',
            'email' => 'max:40',
            'cep' => 'required|min:9',
            'cidade_id' => 'required',
            'contribuinte' => 'required',
        ];
        $messages = [
            'razao_social.required' => 'O Razão social/Nome é obrigatório.',
            'razao_social.max' => '50 caracteres maximos permitidos.',
            'nome_fantasia.required' => 'O campo Nome Fantasia é obrigatório.',
            'nome_fantasia.max' => '80 caracteres maximos permitidos.',
            'cpf_cnpj.required' => 'O campo CPF/CNPJ é obrigatório.',
            'cpf_cnpj.min' => strlen($doc) > 14 ? 'Informe 14 números para CNPJ.' : 'Informe 14 números para CPF.',
            'rua.required' => 'O campo Rua é obrigatório.',
            'rua.max' => '80 caracteres maximos permitidos.',
            'numero.required' => 'O campo Numero é obrigatório.',
            'cep.required' => 'O campo CEP é obrigatório.',
            'cep.min' => 'CEP inválido.',
            'cidade_id.required' => 'O campo Cidade é obrigatório.',
            'numero.max' => '10 caracteres maximos permitidos.',
            'bairro.required' => 'O campo Bairro é obrigatório.',
            'bairro.max' => '50 caracteres maximos permitidos.',
            'telefone.required' => 'O campo Telefone é obrigatório.',
            'telefone.max' => '20 caracteres maximos permitidos.',
            'consumidor_final.required' => 'O campo Consumidor final é obrigatório.',
            'contribuinte.required' => 'O campo Contribuinte é obrigatório.',
            'celular.max' => '20 caracteres maximos permitidos.',
            'email.required' => 'O campo Email é obrigatório.',
            'email.max' => '40 caracteres maximos permitidos.',
            'email.email' => 'Email inválido.',
            'rua_cobranca.max' => '80 caracteres maximos permitidos.',
            'numero_cobranca.max' => '10 caracteres maximos permitidos.',
            'bairro_cobranca.max' => '30 caracteres maximos permitidos.',
            'cep_cobranca.max' => '9 caracteres maximos permitidos.',
        ];
        $this->validate($request, $rules, $messages);
    }

    public function destroy($id)
    {
        $item = Fornecedor::findOrFail($id);
        if (!__valida_objeto($item)) {
            abort(403);
        }
        try {
            $item->delete();
            session()->flash("flash_sucesso", "Fornecedor removido!");
        } catch (\Exception $e) {
            session()->flash("flash_erro", "Algo deu errado: " . $e->getMessage());
            __saveLogError($e, request()->empresa_id);
        }
        return redirect()->route('fornecedores.index');
    }
}
