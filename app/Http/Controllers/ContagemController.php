<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Estoque; // Sua tabela de estoque

class ContagemController extends Controller
{
    public function bipar(Request $request)
    {
        $codBarras = $request->codBarras;

        $produto = Estoque::where('produto_id', $codBarras)->first(); // ou onde você armazena o código de barras

        if(!$produto){
            return response()->json([
                'error' => true,
                'message' => 'Produto não encontrado!'
            ]);
        }

        // Atualiza quantidade automaticamente se quiser
        $produto->quantidade += 1;
        $produto->save();

        return response()->json([
            'error' => false,
            'produto' => $produto->produto_id, // ou nome do produto
            'quantidade_atual' => $produto->quantidade
        ]);
    }
}
