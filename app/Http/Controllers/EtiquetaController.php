<?php

namespace App\Http\Controllers;

use App\Models\Etiqueta;
use Illuminate\Http\Request;

class EtiquetaController extends Controller
{
   public function index(Request $request)
{
    $data = Etiqueta::where('empresa_id', $request->empresa_id)
        ->paginate(env("PAGINACAO"));

    return view('etiquetas.index', compact('data'));
}


    public function create()
    {
        return view('etiquetas.create');
    }

    public function store(Request $request)
    {
        $usuario = get_id_user();
        try {
            $request->merge([
                'nome_empresa' => $request->nome_empresa ? true : false,
                'nome_produto' => $request->nome_produto ? true : false,
          // 🎨 CORRIGIDO: Agora armazena o código de cor (string) ou NULL
            'cor_fundo' => $request->cor_fundo ?? null, 
            'cor_fonte' => $request->cor_fonte ?? null,
                'valor_produto' => $request->valor_produto ? true : false,
                'codigo_produto' => $request->codigo_produto ? true : false,
                'codigo_barras_numerico' => $request->codigo_barras_numerico ? true : false,
                'observacao' => $request->observacao ?? '',
                'empresa_id' => $request->empresa_id 
            ]);
            Etiqueta::create($request->all());
            session()->flash('flash_sucesso', "Etiqueta cadastrada");
            if (isSuper($usuario)) {
                return redirect()->route('etiquetas.index');
            } else {
                return redirect()->route('etiquetas.index');
            }
        } catch (\Exception $e) {
            session()->flash('flash_erro', $e->getMessage());
        }
    }

    public function edit($id)
    {
        $item = Etiqueta::findOrFail($id);
        if (!__valida_objeto($item)) {
            abort(403);
        }
        return view('etiquetas.edit', compact('item'));
    }

    public function update(Request $request, $id)
    {
        $item = Etiqueta::findOrFail($id);
        try {
            $request->merge([
                'nome_empresa' => $request->nome_empresa ? true : false,
                'nome_produto' => $request->nome_produto ? true : false,
            // 🎨 CORRIGIDO: Agora armazena o código de cor (string) ou NULL
            'cor_fundo' => $request->cor_fundo ?? null, 
            'cor_fonte' => $request->cor_fonte ?? null,
                'valor_produto' => $request->valor_produto ? true : false,
                'codigo_produto' => $request->codigo_produto ? true : false,
                'codigo_barras_numerico' => $request->codigo_barras_numerico ? true : false,
                'observacao' => $request->observacao ?? '',
                'empresa_id' => $request->empresa_id 
            ]);
            $item->fill($request->all())->save();
            session()->flash('flash_sucesso', "Etiqueta alterada");
        } catch (\Exception $e) {
            session()->flash('flash_erro', $e->getMessage());
        }
        return redirect()->route('etiquetas.index');
    }

    public function destroy($id)
    {
        $item = Etiqueta::findOrFail($id);
        if (!__valida_objeto($item)) {
            abort(403);
        }
        try {
            $item->delete();
            session()->flash('flash_sucesso', 'Deletado com sucesso!');
        } catch (\Exception $e) {
            session()->flash('flash_erro', 'Algo deu errado' . $e->getMessage());
        }
        return redirect()->route('etiquetas.index');
    }
}
