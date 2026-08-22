<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\NaturezaOperacao;
use Illuminate\Support\Facades\DB;

class NaturezaController extends Controller
{
    public function index(Request $request)
    {
        $data = NaturezaOperacao::where('empresa_id', $request->empresa_id)
        ->when(!empty($request->natureza), function ($q) use ($request) {
            return  $q->where(function ($quer) use ($request) {
                return $quer->where('natureza', 'LIKE', "%$request->natureza%");
            });
        })
        ->paginate(env("PAGINACAO"));

        return view('naturezas/index', compact('data'));
    }

    public function create()
    {
        return view('naturezas/create');
    }

    public function edit($id)
    {
        $item = NaturezaOperacao::findOrFail($id);
        if (!__valida_objeto($item)) {
            abort(403);
        }
        return view('naturezas/edit', compact('item'));
    }

    public function store(Request $request)
    {
        $this->_validate($request);
        try {
            DB::transaction(function () use ($request) {
                NaturezaOperacao::create($request->all());
            });
            session()->flash("flash_sucesso", "Natureza de operação cadastrada!");
        } catch (\Exception $e) {
            session()->flash("flash_erro", "Algo deu errado: " . $e->getMessage());
            __saveLogError($e, request()->empresa_id);
        }
        return redirect()->route('naturezas.index');
    }

    public function update(Request $request, $id)
    {
        $this->_validate($request);
        $item = NaturezaOperacao::findOrFail($id);
        try {
            DB::transaction(function () use ($request, $item) {
                $item->fill($request->all())->save();
            });
            session()->flash("flash_sucesso", "Natureza de operação atualizada!");
        } catch (\Exception $e) {
            session()->flash("flash_erro", "Algo deu errado: " . $e->getMessage());
            __saveLogError($e, request()->empresa_id);
        }
        return redirect()->route('naturezas.index');
    }

    public function destroy($id)
    {
        $item = NaturezaOperacao::findOrFail($id);
        if (!__valida_objeto($item)) {
            abort(403);
        }
        try {
            $item->delete();
            session()->flash("flash_sucesso", "Natureza de operação removida!");
        } catch (\Exception $e) {
            session()->flash("flash_erro", "Algo deu errado: " . $e->getMessage());
            __saveLogError($e, request()->empresa_id);
        }
        return redirect()->route('naturezas.index');
    }

    private function _validate(Request $request)
    {
        $rules = [
            'natureza' => 'required|max:80',
            'CFOP_entrada_estadual' => 'required|min:4',
            'CFOP_entrada_inter_estadual' => 'required|min:4',
            'CFOP_saida_estadual' => 'required|min:4',
            'CFOP_saida_inter_estadual' => 'required|min:4',
        ];
        $messages = [
            'natureza.required' => 'O campo nome é obrigatório.',
            'natureza.max' => '80 caracteres maximos permitidos.',
            'CFOP_entrada_estadual.required' => 'Campo obritatório.',
            'CFOP_entrada_estadual.min' => 'Minimo de 4 digitos.',
            'CFOP_entrada_inter_estadual.required' => 'Campo obritatório.',
            'CFOP_entrada_inter_estadual.min' => 'Minimo de 4 digitos.',
            'CFOP_saida_estadual.required' => 'Campo obritatório.',
            'CFOP_saida_estadual.min' => 'Minimo de 4 digitos.',
            'CFOP_saida_inter_estadual.required' => 'Campo obritatório.',
            'CFOP_saida_inter_estadual.min' => 'Minimo de 4 digitos.',
        ];
        $this->validate($request, $rules, $messages);
    }
}
