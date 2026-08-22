<?php

namespace App\Http\Controllers;

use App\Models\BairroDelivery;
use App\Models\ConfigNota;
use Illuminate\Http\Request;

class BairroDeliveryController extends Controller
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

    // Obtém os bairros relacionados ao user_id da configuração de delivery
    $query = BairroDelivery::where('user_id', $userId);

    // Aplica o filtro se o nome do bairro foi informado
    if ($request->has('nome') && $request->nome) {
        $query->where('bairro', 'LIKE', "%$request->nome%"); // Filtro pelo nome do bairro
    }

    // Aplica a paginação
    $data = $query->paginate(env("PAGINACAO"));

    // Retorna a visão com os dados
    return view('bairros_delivery.index', compact('data'));
}


    // Exibe o formulário de criação de um novo bairro
    public function create(Request $request)
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

        // Retorna a visão de criação com as cidades
        return view('bairros_delivery.create');
    }

    // Armazena um novo bairro no banco de dados
    public function store(Request $request)
    {
        try {
            // Verifica a configuração da empresa
            $config = ConfigNota::where('empresa_id', $request->empresa_id)->first();

            if (!$config) {
                return response()->json([
                    'data' => [],
                ]);
            }

            // Obtém o user_id
            $userId = $config->user_id;

            // Adiciona o ID da cidade no request antes de salvar
            $request->merge([
                'user_id' => $userId,
            ]);

            // Cria o bairro com os dados recebidos
            BairroDelivery::create($request->all());

            // Mensagem de sucesso
            session()->flash('flash_sucesso', 'Cadastrado com sucesso!');
        } catch (\Exception $e) {
            // Se houver erro, exibe a mensagem de erro
            session()->flash('flash_erro', 'Algo deu errado: ' . $e->getMessage());
            __saveLogError($e, $request->empresa_id); // Salva o erro nos logs
        }

        // Redireciona para a lista de bairros
        return redirect()->route('bairrosDelivery.index');
    }

    // Exibe o formulário de edição de um bairro existente
    public function edit($id, Request $request)
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

        // Encontra o bairro pelo ID
        $item = BairroDelivery::findOrFail($id);

        // Valida se o objeto foi encontrado
        if (!$item) {
            abort(403);
        }

        // Retorna a visão de edição com os dados do bairro
        return view('bairros_delivery.edit', compact('item'));
    }

    // Atualiza os dados de um bairro existente
    public function update(Request $request, $id)
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
        
        $item = BairroDelivery::findOrFail($id);

        try {
            // Adiciona o ID da cidade no request antes de salvar
            $request->merge([
                'user_id' => $userId,
            ]);

            // Atualiza os dados do bairro
            $item->fill($request->all())->save();

            // Mensagem de sucesso
            session()->flash('flash_sucesso', 'Atualizado com sucesso!');
        } catch (\Exception $e) {
            // Se houver erro, exibe a mensagem de erro
            session()->flash('flash_erro', 'Algo deu errado: ' . $e->getMessage());
            __saveLogError($e, $request->empresa_id); // Salva o erro nos logs
        }

        // Redireciona para a lista de bairros
        return redirect()->route('bairrosDelivery.index');
    }

  // Deleta um bairro existente
public function destroy($id, Request $request)
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
    
    // Busca o bairro a ser excluído
    $item = BairroDelivery::findOrFail($id); // findOrFail já retorna 404 se não encontrar o registro

    try {
        // Deleta o bairro
        $item->delete();

        // Mensagem de sucesso
        session()->flash('flash_sucesso', 'Deletado com sucesso!');
    } catch (\Exception $e) {
        // Se houver erro, exibe a mensagem de erro
        session()->flash('flash_erro', 'Algo deu errado: ' . $e->getMessage());
        __saveLogError($e, $request->empresa_id); // Salva o erro nos logs
    }

    // Redireciona para a lista de bairros
    return redirect()->route('bairrosDelivery.index');
}
}
