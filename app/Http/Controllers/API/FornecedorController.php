<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Cidade;
use Illuminate\Http\Request;
use App\Models\Fornecedor;

class FornecedorController extends Controller
{
    public function pesquisa(Request $request)
    {
        $data = Fornecedor::orderBy('razao_social', 'desc')
            ->where('empresa_id', $request->empresa_id)
            ->where('razao_social', 'like', "%$request->pesquisa%")
            ->get();
        return response()->json($data, 200);
    }

    public function find($id)
    {
        $fornecedor = Fornecedor::where('id', $id)
            ->first();
        echo json_encode($this->insertCidade($fornecedor));
    }

    private function insertCidade($fornecedor)
    {
        $cidade = Cidade::getId($fornecedor->cidade_id);
        $fornecedor['nome_cidade'] = $cidade->nome;
        return $fornecedor;
    }

    public function store(Request $request)
    {
        try {
            $request->merge([
                'ie_rg' => $request->ie_rg ?? '',
                'telefone' => $request->telefone ?? '',
                'celular' => $request->celular ?? '',
                'email' => $request->email ?? '',
            ]);
            $item = Fornecedor::create($request->all());
            return response()->json($item, 200);
        } catch (\Exception $e) {
            return response()->json($e->getMessage(), 400);
        }
    }
}
