<?php

namespace App\Http\Controllers;

use App\Models\ConfigNota;
use App\Models\Inventario;
use App\Models\ItemInventario;
use Illuminate\Http\Request;
use Dompdf\Dompdf;


class InventarioController extends Controller
{
    public function index(Request $request)
    {
        $start_date = $request->get('start_date');
        $end_date = $request->get('end_date');
        $data = Inventario::where('empresa_id', $request->empresa_id)
            ->when(!empty($start_date), function ($query) use ($start_date) {
                return $query->whereDate('created_at', '>=', $start_date);
            })
            ->when(!empty($end_date), function ($query) use ($end_date) {
                return $query->whereDate('created_at', '<=', $end_date);
            })
            ->orderBy('created_at', 'asc')
            ->paginate(env("PAGINACAO"));
        return view('inventario.index', compact('data'));
    }

    public function create()
    {
        return view('inventario.create');
    }

    public function edit($id)
    {
        $item = Inventario::findOrFail($id);
        if (!__valida_objeto($item)) {
            abort(403);
        }
        return view('inventario.edit', compact('item'));
    }

    public function store(Request $request)
    {
        $this->_validate($request);
        try {
            $request->merge([
                'observacao' => $request->observacao ?? ''
            ]);
            Inventario::create($request->all());
            session()->flash("flash_sucesso", "Cadastrado com sucesso");
        } catch (\Exception $e) {
            session()->flash("flash_erro", "Algo deu Errado" . $e->getMessage());
            __saveLogError($e, request()->empresa_id);
        }
        return redirect()->route('inventario.index');
    }

    public function update(Request $request, $id)
    {
        $this->_validate($request);
        $item = Inventario::findOrFail($id);
        try {
            $request->merge([
                'observacao' => $request->observacao ?? ''
            ]);
            $item->fill($request->all())->save();
            session()->flash("flash_sucesso", "Atualizado com sucesso!");
        } catch (\Exception $e) {
            session()->flash("flash_erro", "Algo deu Errado" . $e->getMessage());
            __saveLogError($e, request()->empresa_id);
        }
        return redirect()->route('inventario.index');
    }

    private function _validate($request)
    {
        $rules = [
            'referencia' => 'required',
            'inicio' => 'required',
            'fim' => 'required'
        ];
        $message = [
            'referencia.required' => 'Campo obrigatório',
            'inicio.required' => 'Campo obrigatório',
            'fim.required' => 'Campo obrigatório'
        ];
        $this->validate($request, $rules, $message);
    }

    public function apontar($id)
    {
        $item = Inventario::findOrFail($id);
        if (!__valida_objeto($item)) {
            abort(403);
        }
        return view('inventario.apontamento', compact('item'));
    }

    public function storeApontamento(Request $request)
    {
        $this->__validateItem($request);
        try {

            $request->merge([
                'usuario_id' => get_id_user(),
                'observacao' => $request->observacao ?? ''
            ]);
            ItemInventario::create($request->all());
            session()->flash("flash_sucesso", "Produto apontado!");
        } catch (\Exception $e) {
            session()->flash("flash_erro", "Algo deu errado" . $e->getMessage());
            __saveLogError($e, request()->empresa_id);
        }
        return redirect()->back();
    }

    private function __validateItem(Request $request)
    {
        $rules = [
            'produto_id' => 'required',
            'quantidade' => 'required',
            'estado' => 'required'
        ];
        $messages = [
            'produto_id.required' => 'O campo produto é obrigatório.',
            'quantidade.required' => 'O campo quantidade é obrigatório.',
            'estado.required' => 'O campo estado é obrigatório.'
        ];
        $this->validate($request, $rules, $messages);
    }

    public function itens(Request $request, $id)
    {
        $inventario = Inventario::findOrFail($id);
        $produto_id = $request->produto_id;
        $itens = ItemInventario::where('inventario_id', $id)
            ->when(!empty($produto_id), function ($query) use ($produto_id) {
                return $query->where('produto_id', $produto_id);
            })
            ->orderBy('created_at', 'asc')
            ->paginate(env("PAGINACAO"));
        $totaliza = $this->totaliza($itens);
        return view('inventario.itens', compact('inventario', 'itens', 'totaliza'));
    }

    private function totaliza($itens)
    {
        $soma['compra'] = 0;
        $soma['venda'] = 0;
        $soma['qtd'] = 0;
        foreach ($itens as $e) {
            $soma['compra'] += $e->produto->valor_compra * $e->quantidade;
            $soma['venda'] += $e->produto->valor_venda * $e->quantidade;
            $soma['qtd'] += $e->quantidade;
        }
        return $soma;
    }

    public function print(Request $request, $id)
    {
        try {
            $inventario = Inventario::findOrFail($id);
            if (valida_objeto($inventario)) {
                $itens = ItemInventario::where('inventario_id', $id)
                    ->get();
                $totaliza = $this->totaliza($itens);
                $config = ConfigNota::where('empresa_id', $request->empresa_id)
                    ->first();
                $p = view('inventario.print', compact(
                    'inventario',
                    'totaliza',
                    'config',
                    'itens'
                ));
                // return $p;
                $domPdf = new Dompdf(["enable_remote" => true]);
                $domPdf->loadHtml($p);
                $pdf = ob_get_clean();
                $domPdf->setPaper("A4");
                $domPdf->render();
                $domPdf->stream("Inventario $id.pdf", array("Attachment" => false));
            }
        } catch (\Exception $e) {
            echo "Erro: " . $e->getMessage();
        }
    }

    public function destroy(Request $request, $id){
        $item = Inventario::findOrFail($id);
        if (!__valida_objeto($item)) {
            abort(403);
        }
        try{
            $item->itens()->delete();
            $item->delete();
            session()->flash("flash_sucesso", "Registro removido!");
        }catch(\Exception $e){
            session()->flash("flash_erro", "Algo deu Errado: " . $e->getMessage());
            __saveLogError($e, request()->empresa_id);
        }
        return redirect()->back();
    }

    public function destroyItem(Request $request, $id){
        $item = ItemInventario::findOrFail($id);
        
        try{
            $item->delete();
            session()->flash("flash_sucesso", "Item removido!");
        }catch(\Exception $e){
            session()->flash("flash_erro", "Algo deu Errado: " . $e->getMessage());
            __saveLogError($e, request()->empresa_id);
        }
        return redirect()->back();
    }
}
