<?php

namespace App\Http\Controllers;

use App\Models\Veiculo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use function PHPUnit\Framework\returnSelf;

class VeiculoController extends Controller
{
    public function index(Request $request)
    {
        $data = Veiculo::where('empresa_id', request()->empresa_id)
            ->when(!empty($request->marca), function ($q) use ($request) {
                return  $q->where(function ($quer) use ($request) {
                    return $quer->where('marca', 'LIKE', "%$request->marca%");
                });
            })
            ->when(!empty($request->proprietario_documento), function ($q) use ($request) {
                return  $q->where(function ($quer) use ($request) {
                    return $quer->where('proprietario_documento', 'LIKE', "%$request->proprietario_documento%");
                });
            })
            ->paginate(env("PAGINACAO"));
        return view('veiculos/index', compact('data'));
    }

    public function create()
    {
        return view('veiculos.create');
    }

    public function store(Request $request)
    {
        $this->_validate($request);
        try {
            $request->merge([
                'rntrc' => $request->rntrc ?? 0,
                'TAF' => $request->TAF ?? 0,
                'renavam' => $request->renavam ?? '',
                'numero_registro_estadual' => $request->numero_registro_estadual ?? '',
                'tipo' => $request->tipo ?? '',
                'tipo_carroceria' => $request->tipo_carroceria ?? '',
                'tipo_rodado' => $request->tipo_rodado ?? '',
                'proprietario_ie' => $request->proprietario_ie ?? ''
            ]);
            DB::transaction(function () use ($request) {
                Veiculo::create($request->all());
            });
            session()->flash("flash_sucesso", "Cadastrado com Sucesso");
        } catch (\Exception $e) {
            session()->flash("flash_erro", "Algo deu Errado" . $e->getMessage());
            __saveLogError($e, request()->empresa_id);
        }
        return redirect()->route('veiculos.index');
    }

    private function _validate(Request $request)
    {
        $rules = [
            'placa' => 'required',
            'cor' => 'required',
            'marca' => 'required',
            'modelo' => 'required',
            'modelo' => 'required',
            'tara' => 'required',
            'capacidade' => 'required',
            'proprietario_nome' => 'required',
            'proprietario_documento' => 'required',
        ];
        $messages = [
            'placa.required' => 'Campo Obrigatório',
            'cor.required' => 'Campo Obrigatório',
            'marca.required' => 'Campo Obrigatório',
            'modelo.required' => 'Campo Obrigatório',
            'tara.required' => 'Campo Obrigatório',
            'capavidade.required' => 'Campo Obrigatório',
            'proprietario_nome.required' => 'Campo Obrigatório',
            'proprietario_documento.required' => 'Campo Obrigatório',
            'capacidade.required' => 'Campo Obrigatório',
        ];
        $this->validate($request, $rules, $messages);
    }

    public function edit($id)
    {
        $item = Veiculo::findOrFail($id);
        if (!__valida_objeto($item)) {
            abort(403);
        }
        return view('veiculos.edit', compact('item'));
    }

    public function update(Request $request, $id)
    {
        $this->_validate($request);
        $item = Veiculo::findOrFail($id);
        try {
            DB::transaction(function () use ($request, $item) {
                $item->fill($request->all())->save();
            });
            session()->flash("flash_sucesso", "Alterado com Sucesso");
        } catch (\Exception $e) {
            session()->flash("flash_erro", "Algo deu Errado" . $e->getMessage());
            __saveLogError($e, request()->empresa_id);
        }
        return redirect()->route('veiculos.index');
    }

    public function destroy($id)
    {
        $item = Veiculo::findOrFail($id);
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
        return redirect()->route('veiculos.index');
    }
}
