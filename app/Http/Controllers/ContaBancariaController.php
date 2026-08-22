<?php

namespace App\Http\Controllers;

use App\Models\Cidade;
use App\Models\ContaBancaria;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


class ContaBancariaController extends Controller
{
    public function index(Request $request)
    {
        $data = ContaBancaria::where('empresa_id', request()->empresa_id)
            ->when(!empty($request->conta), function ($q) use ($request) {
                return $q->where(function ($quer) use ($request) {
                    return $quer->where('conta', 'LIKE', "%$request->conta%");
                });
            })
            ->paginate(env("PAGINACAO"));
        return view('contas_bancarias.index', compact('data'));
    }

    public function create()
    {
        $cidades = Cidade::all();
        return view('contas_bancarias.create', compact('cidades'));
    }

    public function store(Request $request)
    {
        $this->_validate($request);
        try {
            $request->merge([
                'juros' => __convert_value_bd($request->juros),
                'multa' => __convert_value_bd($request->multa),
                'juros' => $request->juros ?? '0',
                'multa' => $request->multa ?? '0',
                'juros_apos' => $request->juros_apos ?? '0'
            ]);
            DB::transaction(function () use ($request) {
                ContaBancaria::create($request->all());
            });
            session()->flash("flash_sucesso", "Cadastro com Sucesso");
        } catch (\Exception $e) {
            session()->flash("flash_erro", "Algo deu Errado" . $e->getMessage());
            __saveLogError($e, request()->empresa_id);
        }
        return redirect()->route('contaBancaria.index');
    }

    private function _validate(Request $request)
    {
        $rules = [
            'agencia' => 'required',
            'conta' => 'required',
            'titular' => 'required',
            'cnpj' => 'required',
            'endereco' => 'required',
            'bairro' => 'required',
            'cep' => 'required',
            'carteira' => 'required',
            'convenio' => 'required'
        ];
        $message = [
            'agencia.required' => 'Campo Obrigatório',
            'conta.required' => 'Campo Obrigatório',
            'titular.required' => 'Campo Obrigatório',
            'cnpj.required' => 'Campo Obrigatório',
            'endereco.required' => 'Campo Obrigatório',
            'bairro.required' => 'Campo Obrigatório',
            'cep.required' => 'Campo Obrigatório',
            'carteira.required' => 'Campo Obrigatório',
            'convenio.required' => 'Campo Obrigatório'
        ];
        $this->validate($request, $rules, $message);
    }

    public function edit($id)
    {
        $item = ContaBancaria::findOrFail($id);
        if (!__valida_objeto($item)) {
            abort(403);
        }
        $cidades = Cidade::all();
        return view('contas_bancarias.edit', compact('item', 'cidades'));
    }

    public function update(Request $request, $id)
    {
        $this->_validate($request);
        $item = ContaBancaria::findOrfail($id);
        try {
            $request->merge([
                'juros' => __convert_value_bd($request->juros),
                'multa' => __convert_value_bd($request->multa),
                'juros' => $request->juros ?? '0',
                'multa' => $request->multa ?? '0',
                'juros_apos' => $request->juros_apos ?? '0'
            ]);
            DB::transaction(function () use ($request, $item) {
                $item->fill($request->all())->save();
            });
            session()->flash("flash_sucesso", "Cadastro com Sucesso");
        } catch (\Exception $e) {
            session()->flash("flash_erro", "Algo deu Errado" . $e->getMessage());
            __saveLogError($e, request()->empresa_id);
        }
        return redirect()->route('contaBancaria.index');
    }

    public function destroy(Request $request, $id)
    {
        $item = ContaBancaria::findOrFail($id);
        if (!__valida_objeto($item)) {
            abort(403);
        }
        try {
            $item->delete();
            session()->flash("flash_sucesso", "Deletado com Sucesso");
        } catch (\Exception $e) {
            session()->flash("flash_erro", "Algo deu Errado" . $e->getMessage());
            __saveLogError($e, request()->empresa_id);
        }
        return redirect()->route('contaBancaria.index');
    }
}
