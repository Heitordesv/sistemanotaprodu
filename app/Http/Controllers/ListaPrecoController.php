<?php

namespace App\Http\Controllers;

use App\Models\ListaPreco;
use App\Models\Produto;
use App\Models\ProdutoListaPreco;
use Illuminate\Http\Request;

class ListaPrecoController extends Controller
{
    public function index(Request $request)
    {
        $data = ListaPreco::where('empresa_id', request()->empresa_id)
            ->when(!empty($request->nome), function ($q) use ($request) {
                return  $q->where(function ($quer) use ($request) {
                    return $quer->where('nome', 'LIKE', "%$request->nome%");
                });
            })
            ->paginate(env("PAGINACAO"));
        return view('lista_preco.index', compact('data'));
    }

    public function create()
    {
        return view('lista_preco.create');
    }

    public function store(Request $request)
    {
        $this->_validate($request);
        try {
            $request->merge([
                'percentual_alteracao' => __convert_value_bd($request->percentual_alteracao)
            ]);
            ListaPreco::create($request->all());
            session()->flash("flash_sucesso", "Cadastro com Sucesso");
        } catch (\Exception $e) {
            session()->flash("flash_erro", "Algo deu Errado" . $e->getMessage());
            __saveLogError($e, request()->empresa_id);
        }
        return redirect()->route('listaDePrecos.index');
    }

    private function _validate(Request $request)
    {
        $rules = [
            'nome' =>  'required',
            'percentual_alteracao' => 'required'
        ];
        $messages = [
            'nome.required' => 'Campo Obrigatório',
            'percentual_alteracao.required' => 'Campo Obrigatório'
        ];
        $this->validate($request, $rules, $messages);
    }

    public function edit($id)
    {
        $item = ListaPreco::findOrFail($id);
        if (!__valida_objeto($item)) {
            abort(403);
        }
        return view('lista_preco.edit', compact('item'));
    }

    public function update(Request $request, $id)
    {
        $this->_validate($request);
        $item = ListaPreco::findOrFail($id);
        try {

            $request->merge([
                'percentual_alteracao' => __convert_value_bd($request->percentual_alteracao)
            ]);
            $item->fill($request->all())->save();
            session()->flash("flash_sucesso", "Cadastro com Sucesso");
        } catch (\Exception $e) {
            session()->flash("flash_erro", "Algo deu Errado" . $e->getMessage());
            __saveLogError($e, request()->empresa_id);
        }
        return redirect()->route('listaDePrecos.index');
    }

    public function destroy($id)
    {
        $item = ListaPreco::findOrFail($id);
        if (!__valida_objeto($item)) {
            abort(403);
        }
        try {
            $item->delete();
            session()->flash("flash_sucesso", "Marca removida!");
        } catch (\Exception $e) {
            session()->flash("flash_erro", "Algo deu errado: " . $e->getMessage());
            __saveLogError($e, request()->empresa_id);
        }
        return redirect()->route('listaDePrecos.index');
    }

 public function pesquisa(Request $request)
{
    $listas = ListaPreco::where('empresa_id', $request->empresa_id)->get();

    // Exemplo: se quiser filtrar por nome enviado no request
    $resultados = ListaPreco::where('empresa_id', $request->empresa_id)
                     ->where('nome', 'like', '%' . $request->nome . '%')
                     ->get();

    return view('lista_preco.pesquisa', compact('listas', 'resultados'));
}

    public function show(Request $request, $id)
    {
        $produtos = Produto::where('empresa_id', $request->empresa_id)->get();
        if (!__valida_objeto($produtos)) {
            abort(403);
        }
        $data = ListaPreco::findOrFail($id);
        return view('lista_preco.show', compact('data', 'produtos'));
    }

    public function gerar(Request $request, $id)
    {
        $produtos = Produto::where('empresa_id', $request->empresa_id)->get();
        $lista = ListaPreco::findOrFail($id);
        ProdutoListaPreco::where('lista_id', $id)->delete();
        if (valida_objeto($lista)) {
            foreach ($produtos as $p) {
                $valorCompra = $p->valor_compra;
                $valorVenda = $p->valor_venda;
                $valor = 0;
                if ($lista->tipo_inc_red == 1) {
                    if ($valorCompra > 0 && $lista->tipo == 1) {
                        $valor = $valorCompra + (($valorCompra * $lista->percentual_alteracao) / 100);
                    } else {
                        $valor = $valorVenda + (($valorVenda * $lista->percentual_alteracao) / 100);
                    }
                } else {
                    if ($valorCompra > 0 && $lista->tipo == 1) {
                        $valor = $valorCompra - (($valorCompra * $lista->percentual_alteracao) / 100);
                    } else {
                        $valor = $valorVenda - (($valorVenda * $lista->percentual_alteracao) / 100);
                    }
                }
                $data = [
                    'valor_venda' => $p->valor_venda,
                    'lista_id' => $id,
                    'produto_id' => $p->id,
                    'percentual_lucro' => $lista->percentual_alteracao,
                    'valor' => $valor
                ];
                $res = ProdutoListaPreco::create($data);
            }
            session()->flash("flash_sucesso", "Produtos cadastrados na lista $lista->nome");
            return redirect()->back();
        } else {
            return redirect('/403');
        }
    }

    public function editValor($id)
    {
        $item = ProdutoListaPreco::findOrFail($id);
        if (!__valida_objeto($item)) {
            abort(403);
        }
        return view('lista_preco.editarValor', compact('item'));
    }

    public function storeValor(Request $request)
    {
        try {
            $produto = ProdutoListaPreco::findOrFail($request->id);
            $valorLucro = 0;
            $valorCompra = $produto->produto->valor_compra;
            $valorVenda = $produto->produto->valor_venda;
            $novoValor = __convert_value_bd($request->novo_valor);
            if ($produto->lista->tipo == 1) {
                if ($valorCompra > $novoValor) {
                    $valorLucro = (($valorCompra - $novoValor) / $novoValor) * 100;
                } else {
                    $valorLucro = (($novoValor - $valorCompra) / $valorCompra) * 100;
                }
            } else {
                if ($valorVenda > $novoValor) {
                    $valorLucro = (($valorVenda - $novoValor) / $novoValor) * 100;
                } else {
                    $valorLucro = (($novoValor - $valorVenda) / $valorVenda) * 100;
                }
            }
            // echo $valorLucro;
            $produto->valor = $novoValor;
            $produto->percentual_lucro = $valorLucro;
            $produto->save();
            session()->flash("flash_sucesso", "Valor atualizado do produto " . $produto->produto->nome);
        } catch (\Exception $e) {
            session()->flash("flash_erro", "Algo deu errado" . $e->getMessage());
            __saveLogError($e, request()->empresa_id);
        }
        return redirect()->back();
    }

    public function filtro(Request $request)
    {
        $listas = ListaPreco::where('empresa_id', $request->empresa_id)
            ->get();
        $produto = $request->produto;
        $listaId = $request->lista_id;
        $resultados = Produto::where('nome', 'LIKE', "%$produto%")
            ->where('empresa_id', $request->empresa_id)
            ->get();
        foreach ($resultados as $p) {
            $lista = ProdutoListaPreco::where('lista_id', $listaId)
                ->where('produto_id', $p->id)
                ->first();
            if ($lista && $lista->valor > 0) {
                $p->valor_lista = $lista->valor;
            }
        }
        return view('lista_preco.pesquisa', compact('resultados', 'listas'));
    }
}
