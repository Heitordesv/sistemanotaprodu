<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Cat_ws;
use App\Models\ConfigNota;
use Illuminate\Http\Request;
use App\Utils\UploadUtil;

class ItemController extends Controller
{
    protected $util;

    public function __construct(UploadUtil $util)
    {
        $this->util = $util;
    }

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
        $data = Item::where('user_id', $userId)
            ->when($request->nome_item, function ($query) use ($request) {
                return $query->where('nome_item', 'LIKE', "%{$request->nome_item}%");
            })
            ->paginate(env('PAGINACAO', 10));

        // Retorna a view com os dados
        return view('itens.index', compact('data'));
    }

public function create(Request $request)
{
    $config = ConfigNota::where('empresa_id', $request->empresa_id)->first();

    if (!$config) {
        return response()->json([
            'data' => [],
        ]);
    }

    $userId = $config->user_id;

    $categorias = Cat_ws::where('user_id', $userId)->get();

    return view('itens.create', compact('categorias'));
}
public function store(Request $request)
{
    try {
        $request->validate([
            'dia_semana'  => 'required|array',
            'dia_semana.*'=> 'string',
        ]);

        $data = $request->all();

        if (in_array('Todos', $data['dia_semana'])) {
            $data['dia_semana'] = array_diff($data['dia_semana'], ['Todos']);
        }

        // Sanitiza os valores: remove espaços e aspas de cada dia
        $diasLimpos = array_map(function($dia) {
            return trim($dia, " \"'");
        }, $data['dia_semana']);

        $data['dia_semana'] = implode(',', $diasLimpos);

        $config = ConfigNota::where('empresa_id', $request->empresa_id)->first();
        if (!$config) {
            session()->flash('flash_erro', 'Configuração da empresa não encontrada.');
            return back()->withInput();
        }

        $data['user_id'] = $config->user_id;

        if ($request->hasFile('img_item') && $request->file('img_item')->isValid()) {
            $image = $request->file('img_item');
            $imageName = uniqid() . '.' . $image->getClientOriginalExtension();
            $destinationPath = public_path('uploads/images');
            if (!file_exists($destinationPath)) {
                mkdir($destinationPath, 0777, true);
            }
            $image->move($destinationPath, $imageName);
            $data['img_item'] = 'uploads/images/' . $imageName;
        }

        Item::create($data);

        session()->flash('flash_sucesso', 'Item cadastrado com sucesso!');
        return redirect()->route('itens.index');

    } catch (\Exception $e) {
        session()->flash('flash_erro', 'Erro ao cadastrar item: ' . $e->getMessage());
        return back()->withInput();
    }
}

public function edit(Request $request, $id)
{
    $config = ConfigNota::where('empresa_id', $request->empresa_id)->first();
    if (!$config) {
        session()->flash('flash_erro', 'Configuração da empresa não encontrada.');
        return back()->withInput();
    }

    $item = Item::where('id', $id)
                ->where('user_id', $config->user_id)
                ->first();

    if (!$item) {
        session()->flash('flash_erro', 'Item não encontrado ou você não tem permissão para editá-lo.');
        return back();
    }

    $categorias = Cat_ws::where('user_id', $config->user_id)->get();

    return view('itens.edit', compact('item', 'categorias'));
}

public function atualizarDisponibilidade(Request $request)
{
    try {
        $item = Item::find($request->iditem);

        if ($item) {
            $item->disponivel = !$item->disponivel;
            $item->save();

            session()->flash('flash_sucesso', 'Disponibilidade do item atualizada com sucesso!');
            return back();
        }

        session()->flash('flash_erro', 'Item não encontrado!');
        return back();
    } catch (\Exception $e) {
        session()->flash('flash_erro', 'Erro ao atualizar item: ' . $e->getMessage());
        return back();
    }
}

public function destroy($id)
{
    $item = Item::findOrFail($id);
    $item->delete();

    session()->flash('flash_sucesso', 'Item excluído com sucesso!');
    return redirect()->route('itens.index');
}

public function update(Request $request, $id)
{
    try {
        // Validação dos dados
        $request->validate([
            'dia_semana'  => 'required|array',
            'dia_semana.*'=> 'string',
        ]);

        // Obtendo todos os dados da requisição
        $data = $request->all();

        // Remover o valor 'Todos', caso esteja presente
        if (in_array('Todos', $data['dia_semana'])) {
            $data['dia_semana'] = array_diff($data['dia_semana'], ['Todos']);
        }

        // Sanitizando os valores: remove aspas simples/dobras e espaços extras
        $diasLimpos = array_map(function($dia) {
            // Remove qualquer tipo de aspas (simples ou duplas) e espaços extras
            // Adicionando um passo para remover também qualquer valor inesperado de aspas
            $dia = trim(str_replace(['"', "'"], '', $dia)); // Remove aspas duplas e simples
            return $dia;
        }, $data['dia_semana']);

        // Remover duplicatas, se houver
        $diasLimpos = array_unique($diasLimpos);

        // Garantir que os dias sejam salvos sem aspas
        // Não adiciona aspas no momento de salvar a string, somente os valores puros
        $data['dia_semana'] = implode(',', $diasLimpos);

        // Buscando o item no banco de dados
        $item = Item::findOrFail($id);

        // Verificando se há uma imagem e processando a imagem
        if ($request->hasFile('img_item') && $request->file('img_item')->isValid()) {
            $image = $request->file('img_item');
            $imageName = uniqid() . '.' . $image->getClientOriginalExtension();
            $destinationPath = public_path('uploads/images');
            if (!file_exists($destinationPath)) {
                mkdir($destinationPath, 0777, true);
            }
            $image->move($destinationPath, $imageName);
            $data['img_item'] = 'uploads/images/' . $imageName;
        }

        // Atualizando o item no banco de dados
        $item->update($data);

        // Mensagem de sucesso
        session()->flash('flash_sucesso', 'Item atualizado com sucesso!');
        return redirect()->route('itens.index');

    } catch (\Exception $e) {
        // Mensagem de erro
        session()->flash('flash_erro', 'Erro ao atualizar item: ' . $e->getMessage());
        return back()->withInput();
    }
}


    private function __validate(Request $request)
    {
        // Validação dos campos de entrada
        $request->validate([
            'nome_item' => 'required|string|max:255',
            'preco_item' => 'required|numeric',
            'descricao_item' => 'nullable|string',
            'img_item' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);
    }
}
