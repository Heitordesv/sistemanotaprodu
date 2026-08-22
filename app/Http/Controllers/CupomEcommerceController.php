<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CupomDescontoEcommerce;

class CupomEcommerceController extends Controller
{
    public function index(Request $request)
    {
        $data = CupomDescontoEcommerce::where('empresa_id', $request->empresa_id)
            ->paginate(30);

        return view('cupom_ecommerce.index', compact('data'));
    }

    public function create()
    {
        return view('cupom_ecommerce.create');
    }

    public function edit($id)
    {
        $item = CupomDescontoEcommerce::findOrFail($id);
        return view('cupom_ecommerce.edit', compact('item'));
    }

    public function store(Request $request)
    {
        try {
            // Converte valores para decimal do banco
            $valor = str_replace([',', 'R$', '%'], ['.', '', ''], $request->valor);
            $valor_minimo = str_replace([',', 'R$'], ['.', ''], $request->valor_minimo_pedido);

            $request->merge([
                'valor' => $valor,
                'valor_minimo_pedido' => $valor_minimo,
            ]);

            CupomDescontoEcommerce::create($request->all());

            session()->flash('flash_sucesso', 'Cupom adicionado com sucesso!');
        } catch (\Exception $e) {
            session()->flash('flash_erro', 'Algo deu errado! ' . $e->getMessage());
        }

        return redirect()->route('cuponsEcommerce.index');
    }

    public function update(Request $request, $id)
    {
        $item = CupomDescontoEcommerce::findOrFail($id);
        try {
            $valor = str_replace([',', 'R$', '%'], ['.', '', ''], $request->valor);
            $valor_minimo = str_replace([',', 'R$'], ['.', ''], $request->valor_minimo_pedido);

            $request->merge([
                'valor' => $valor,
                'valor_minimo_pedido' => $valor_minimo,
            ]);

            $item->fill($request->all())->save();

            session()->flash('flash_sucesso', 'Cupom atualizado com sucesso!');
        } catch (\Exception $e) {
            session()->flash('flash_erro', 'Algo deu errado! ' . $e->getMessage());
            __saveLogError($e, request()->empresa_id);
        }

        return redirect()->route('cuponsEcommerce.index');
    }

    public function destroy($id)
    {
        $item = CupomDescontoEcommerce::findOrFail($id);
        if (!__valida_objeto($item)) {
            abort(403);
        }

        try {
            $item->delete();
            session()->flash('flash_sucesso', 'Cupom deletado com sucesso!');
        } catch (\Exception $e) {
            session()->flash('flash_erro', 'Algo deu errado! ' . $e->getMessage());
            __saveLogError($e, request()->empresa_id);
        }

        return redirect()->route('cuponsEcommerce.index');
    }
}
