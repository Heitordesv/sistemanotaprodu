<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Produto;
use App\Models\Estoque;

class EstoqueController extends Controller
{
    public function index()
    {
        return view('contagem.index');
    }

    public function bipar(Request $request)
    {
        $request->validate([
            'codBarras'   => 'required|string',
            'empresa_id'  => 'required|integer',
        ]);

        // Adiciona zeros à esquerda para ter 15 caracteres
        $codBarras = str_pad($request->codBarras, 15, '', STR_PAD_LEFT);

        // Busca o produto na empresa específica
        $produto = Produto::where('codBarras', $codBarras)
                          ->where('empresa_id', $request->empresa_id)
                          ->first();

        if (!$produto) {
            return response()->json([
                'error' => true,
                'message' => 'Produto não encontrado nesta empresa!'
            ]);
        }

        // Busca o estoque existente
        $estoque = Estoque::where('produto_id', $produto->id)
                          ->where('empresa_id', $request->empresa_id)
                          ->where('filial_id', $request->filial_id)
                          ->first();

        // Se não existir, cria um novo registro
        if (!$estoque) {
            $estoque = Estoque::create([
                'empresa_id'    => $request->empresa_id,
                'produto_id'    => $produto->id,
                'quantidade'    => 1,
                'valor_compra'  => $produto->valor_compra,
                'filial_id'     => $request->filial_id,
            ]);
        } else {
            // Se existir, incrementa a quantidade
            $estoque->quantidade += 1;
            $estoque->save();
        }

        return response()->json([
            'error'             => false,
            'produto'           => $produto->nome,
            'codBarras'         => $produto->codBarras,
            'quantidade_atual'  => $estoque->quantidade,
        ]);
    }
}
