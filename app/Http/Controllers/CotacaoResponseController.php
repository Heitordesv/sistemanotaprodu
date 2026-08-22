<?php

namespace App\Http\Controllers;

use App\Models\ConfigNota;
use App\Models\Cotacao;
use App\Models\ItemCotacao;
use Illuminate\Http\Request;

class CotacaoResponseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    public function response($link)
    {
        $cotacao = Cotacao::where('link', $link)
            ->where('ativa', true)
            ->where('resposta', false)
            ->first();
        if ($cotacao) {
            $config = ConfigNota::where('empresa_id', $cotacao->empresa_id)->first();
            $logo = $config->logo;
            return view('cotacao.response', compact('config', 'logo', 'cotacao'));
        } else {
            session()->flash("flash_erro", "Cotação finalizada!");
            return redirect()->route('catacao.finish');
        }
    }



    public function finish()
    {
        return view('cotacao.finish');
    }
    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $cotacao = Cotacao::findOrFail($request->cotacao_id);
        try {
            $total = 0;
            for ($i = 0; $i < sizeof($request->valor); $i++) {
                $total += __convert_value_bd($request->valor[$i]) * __convert_value_bd($request->quantidade[$i]);
            }
            $cotacao->valor = $total;
            $cotacao->forma_pagamento = $request->forma_pagamento ?? '';
            $cotacao->responsavel = $request->responsavel ?? '';
            $cotacao->resposta = true;
            $cotacao->save();

            for ($i = 0; $i < sizeof($request->valor); $i++) {
                $item = ItemCotacao::findOrFail($request->item_id[$i]);
                $item->valor = __convert_value_bd($request->valor[$i]) * __convert_value_bd($request->quantidade[$i]);
                $item->save();
            }

            session()->flash("flash_sucesso", "Cotação respondida!");
        } catch (\Exception $e) {
            session()->flash("flash_erro", "Algo deu errado!");
        }
        return redirect()->route('catacao.finish');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
