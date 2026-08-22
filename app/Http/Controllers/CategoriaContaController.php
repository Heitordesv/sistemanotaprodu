<?php

namespace App\Http\Controllers;

use App\Models\CategoriaConta;
use Illuminate\Http\Request;

use function PHPUnit\Framework\returnSelf;

class CategoriaContaController extends Controller
{
    public function index(Request $request)
    {
        $data = CategoriaConta::where('empresa_id', request()->empresa_id)
            ->when(!empty($request->nome), function ($q) use ($request) {
                return  $q->where(function ($quer) use ($request) {
                    return $quer->where('nome', 'LIKE', "%$request->nome%");
                });
            })
            ->paginate(env("PAGINACAO"));
        return view('categorias_conta.index', compact('data'));
    }

    public function create()
    {
        return view('categorias_conta.create');
    }

    public function store(Request $request)
    {
        $this->_validate($request);
        try {
            CategoriaConta::create($request->all());
            session()->flash("flash_sucesso", "Cadastro com Sucesso");
        } catch (\Exception $e) {
            session()->flash("flash_erro", "Algo deu Errado" . $e->getMessage());
            __saveLogError($e, request()->empresa_id);
        }
        return redirect()->route('categoria-conta.index');
    }

    public function edit($id)
    {
        $item = CategoriaConta::findOrFail($id);
        if (!__valida_objeto($item)) {
            abort(403);
        }
        return view('categorias_conta.edit', compact('item'));
    }

    public function update(Request $request, $id)
    {
        $this->_validate($request);
        $item = CategoriaConta::findOrFail($id);
        try {
            $item->fill($request->all())->save();
            session()->flash("flash_sucesso", "Cadastro com Sucesso");
        } catch (\Exception $e) {
            session()->flash("flash_erro", "Algo deu Errado" . $e->getMessage());
            __saveLogError($e, request()->empresa_id);
        }
        return redirect()->route('categoria-conta.index');
    }

    private function _validate(Request $request)
    {
        $rules = [
            'nome' => 'required|max:50'
        ];
        $messages = [
            'nome.required' => 'O campo nome é obrigatório.',
            'nome.max' => '50 caracteres maximos permitidos.'
        ];
        $this->validate($request, $rules, $messages);
    }


    public function destroy($id)
    {
        $item = CategoriaConta::findOrFail($id);
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
        return redirect('categoria-conta');
    }
}
