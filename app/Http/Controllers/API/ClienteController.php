<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Cliente;
use App\Services\LimiteCreditoClienteService;
use Illuminate\Http\Request;

class ClienteController extends Controller
{
    public function pesquisa(Request $request)
    {
        $data = Cliente::orderBy('razao_social', 'desc')
            ->where('empresa_id', $request->empresa_id)
            ->where('razao_social', 'like', "%$request->pesquisa%")
            ->get();

        return response()->json($data, 200);
    }

    public function store(Request $request)
    {
        try {
            $request->merge([
                'ie_rg' => $request->ie_rg ?? '',
                'limite_venda' => $request->limite_venda
                    ? __convert_value_bd($request->limite_venda)
                    : 0,
                'grupo_id' => $request->grupo_id ?? 0,
                'acessor_id' => $request->acessor_id ?? 0,
                'telefone' => $request->telefone ?? '',
                'celular' => $request->celular ?? '',
                'email' => $request->email ?? '',
                'observacao' => $request->observacao ?? '',
                'nome_fantasia' => $request->nome_fantasia ?? '',
            ]);

            $item = Cliente::create($request->all());

            return response()->json($item, 200);
        } catch (\Exception $e) {
            return response()->json($e->getMessage(), 400);
        }
    }

    public function find(Request $request, $id, LimiteCreditoClienteService $creditoService)
    {
        $empresaId = $request->filled('empresa_id')
            ? (int) $request->empresa_id
            : null;

        $cliente = $creditoService->buscarClienteComCredito((int) $id, $empresaId);

        return response()->json($cliente, 200);
    }
}