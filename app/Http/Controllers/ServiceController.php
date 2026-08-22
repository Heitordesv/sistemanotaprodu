<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use App\Models\CategoriaServico;
use App\Models\Servico;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use function PHPUnit\Framework\returnSelf;

class ServiceController extends Controller
{
    public function index(Request $request)
    {
        $data = Servico::where('empresa_id', request()->empresa_id)
            ->when(!empty($request->nome), function ($q) use ($request) {
                return  $q->where(function ($quer) use ($request) {
                    return $quer->where('nome', 'LIKE', "%$request->nome%");
                });
            })
            ->paginate(env("PAGINACAO"));

        return view('servicos/index', compact('data'));
    }

    public function create()
    {
        $categorias = CategoriaServico::where('empresa_id', request()->empresa_id)->get();
        return view('servicos.create', compact('categorias'));
    }

    public function store(Request $request)
    {
        $this->_validate($request);
        try {
            $request->merge([
                'valor' => __convert_value_bd($request->valor),
                'comissao' => __convert_value_bd($request->comissao),
                'comissao' => $request->comissao ?? '0',
                'tempo_tolerancia' => $request->tempo_tolerancia ?? '0',
                'tempo_adicional' => $request->tempo_adicional ?? '0',
                'valor_adicional' => $request->valor_adicional ?? '0'
            ]);
            DB::transaction(function () use ($request) {
                Servico::create($request->all());
            });
            session()->flash("flash_sucesso", "Cadastrado com sucesso!");
        } catch (\Exception $e) {
            session()->flash("flash_erro", "Algo deu errado: " . $e->getMessage());
            __saveLogError($e, request()->empresa_id);
        }
        return redirect()->route('servicos.index');
    }

    private function _validate(Request $request)
    {
        $rules = [
            'nome' => 'required|max:80',
            'valor' => 'required',
            'unidade_cobranca' => 'required',
            'tempo_servico' => 'required',
            'comissao' => 'max:6',
        ];
        $messages = [
            'nome.required' => 'Nome é obrigatório',
            'valor.required' => 'Campo obrigatório',
            'unidade_cobranca.required' => 'Campo obrigatório',
            'tempo_servico.required' => 'Campo obrigatório',
            'comissao.max' => 'No máximo 5 digitos'
        ];
        $this->validate($request, $rules, $messages);
    }

    public function edit($id)
    {
        $categorias = CategoriaServico::where('empresa_id', request()->empresa_id)->get();
        $item = Servico::findOrFail($id);
        return view('servicos.edit', compact('categorias', 'item'));
    }

    public function update(Request $request, $id)
    {
        $this->_validate($request);
        $item = Servico::findOrFail($id);
        try {
            $request->merge([
                'valor' => __convert_value_bd($request->valor),
                'comissao' => __convert_value_bd($request->comissao),
                'tempo_tolerancia' => $request->tempo_tolerancia ?? '0',
                'tempo_adicional' => $request->tempo_adicional ?? '0',
                'valor_adicional' => $request->valor_adicional ?? '0'
            ]);
            DB::transaction(function () use ($request, $item) {
                $item->fill($request->all())->save();
            });
            session()->flash("flash_sucesso", "Cadastro com Sucesso");
        } catch (\Exception $e) {
            session()->flash("flash_erro", "Algo deu errado" . $e->getMessage());
            __saveLogError($e, request()->empresa_id);
        }
        return redirect()->route('servicos.index');
    }

    public function destroy($id)
    {
        try {
            $item = Servico::findOrFail($id);
            $item->delete();
            session()->flash("flash_sucesso", "Removido com sucesso");
        } catch (\Exception $e) {
            session()->flash("flash_erro", "Aldo deu Errado" . $e->getMessage());
            __saveLogError($e, request()->empresa_id);
        }
        return redirect()->route('servicos.index');
    }
}
