<?php

namespace App\Http\Controllers;

use App\Models\Cat_ws;
use App\Models\ConfigNota;
use Illuminate\Http\Request;

class WscatetoriaController extends Controller
{
    public function index(Request $request)
    {
        // Verifica a configuração da empresa
        $config = ConfigNota::where('empresa_id', $request->empresa_id)->first();

        if (!$config) {
            return response()->json([
                'data' => [],
            ]);
        }

        // Obtém o user_id
        $userId = $config->user_id;

        // Aplica o filtro e a paginação
        $data = Cat_ws::where('user_id', $userId)
            ->paginate(env('PAGINACAO'));
        return view('wscat.index', compact('data'));
    }

    public function create()
    {
        // Exibe a tela para criar uma nova categoria
        return view('wscat.create');
    }

public function store(Request $request)
{
    try {
        // Validação dos dias da semana como array
        $request->validate([
            'dia_semana'   => 'required|array',
            'dia_semana.*' => 'string',
        ]);

        $data = $request->all();

        // Se foi selecionado "Todos", remove ele da lista
        if (in_array('Todos', $data['dia_semana'])) {
            $data['dia_semana'] = array_diff($data['dia_semana'], ['Todos']);
        }

        // Limpa os valores (remove espaços e aspas)
        $diasLimpos = array_map(function ($dia) {
            return trim($dia, " \"'");
        }, $data['dia_semana']);

        // Junta os dias em uma string separada por vírgula
        $data['dias_semana'] = implode(',', $diasLimpos);

        // Validação dos demais campos
        $request->validate([
            'nome_cat'        => 'required|string|max:255',
            'hora_abertura'   => 'nullable|date_format:H:i',
            'hora_fechamento' => 'nullable|date_format:H:i',
            'ord'             => 'nullable|integer',
        ]);

        // Busca a configuração da empresa
        $config = ConfigNota::where('empresa_id', $request->empresa_id)->first();
        $userId = $config ? $config->user_id : null;

        if (!$userId) {
            throw new \Exception('User ID não encontrado na configuração da empresa.');
        }

       
        // Criação da categoria
        Cat_ws::create([
            'user_id'         => $userId,
            'nome_cat'        => $request->nome_cat,
            'desc_cat'        => $request->desc_cat,
            'hora_abertura'   => $request->hora_abertura,
            'hora_fechamento' => $request->hora_fechamento,
            'ord'             => $request->ord,
            'dias_semana'     => $data['dias_semana'],
        ]);

        session()->flash('flash_sucesso', 'Categoria cadastrada com sucesso!');
    } catch (\Exception $e) {
        session()->flash('flash_erro', 'Algo deu errado: ' . $e->getMessage());
        __saveLogError($e, $request->empresa_id);
    }

    return redirect()->route('wscat.index');
}


public function edit($id)
{
    $item = Cat_ws::findOrFail($id);

    // Transforma a string em array para usar na view
    $item->dias_semana_array = explode(',', $item->dias_semana);

    return view('wscat.edit', compact('item'));
}

public function update(Request $request, $id)
{
    $item = Cat_ws::findOrFail($id);

    try {
        // Validação dos campos
        $request->validate([
            'nome_cat'        => 'required|string|max:255',
            'ord'             => 'nullable|integer',
            'dia_semana'      => 'required|array',
            'dia_semana.*'    => 'string',
        ]);

        // Processa dias da semana
        $data = $request->all();

        if (in_array('Todos', $data['dia_semana'])) {
            $data['dia_semana'] = array_diff($data['dia_semana'], ['Todos']);
        }

        $diasLimpos = array_map(function ($dia) {
            return trim($dia, " \"'");
        }, $data['dia_semana']);

        $data['dias_semana'] = implode(',', $diasLimpos);

        // Atualiza campos diretamente
        $item->nome_cat        = $request->nome_cat;
       $item->desc_cat        = $request->desc_cat;
        $item->hora_abertura   = $request->hora_abertura;
        $item->hora_fechamento = $request->hora_fechamento;
        $item->ord             = $request->ord;
        $item->dias_semana     = $data['dias_semana'];

        // Atualiza imagem, se enviada
        if ($request->hasFile('icon_cat')) {
            $imageName = time() . '.' . $request->icon_cat->extension();
            $request->icon_cat->move(public_path('images'), $imageName);
            $item->icon_cat = 'images/' . $imageName;
        }

        $item->save();

        session()->flash('flash_sucesso', 'Categoria atualizada com sucesso!');
    } catch (\Exception $e) {
        session()->flash('flash_erro', 'Algo deu errado: ' . $e->getMessage());
        __saveLogError($e, $request->empresa_id);
    }

    return redirect()->route('wscat.index');
}



    public function destroy($id)
    {
        // Encontra a categoria pelo ID
        $item = Cat_ws::findOrFail($id);

    
        try {
            // Deleta a categoria
            $item->delete();

            session()->flash('flash_sucesso', 'Categoria deletada com sucesso!');
        } catch (\Exception $e) {
            session()->flash('flash_erro', 'Algo deu errado: ' . $e->getMessage());
            __saveLogError($e, request()->empresa_id);
        }

        return redirect()->route('wscat.index');
    }
}
