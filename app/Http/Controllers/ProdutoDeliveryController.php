<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use App\Models\CategoriaProdutoDelivery;
use App\Models\DivisaoGrade;
use App\Models\ImagensProdutoDelivery;
use App\Models\Produto;
use App\Models\ProdutoDelivery;
use App\Models\ProdutoPizza;
use App\Models\Tributacao;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Utils\UploadUtil;
use App\Utils\Util;

class ProdutoDeliveryController extends Controller
{
    protected $util;

    public function __construct(UploadUtil $util)
    {
        $this->util = $util;
    }

    public function index(Request $request)
    {
        $data = ProdutoDelivery::where('empresa_id', $request->empresa_id)
            ->when(!empty($request->nome), function ($q) use ($request) {
                return $q->where(function ($quer) use ($request) {
                    return $quer->where('nome', 'LIKE', "%$request->nome%");
                });
            })
            ->paginate(env("PAGINACAO"));
        return view('produtos_delivery.index', compact('data'));
    }

    public function create(Request $request)
    {
        $dataValidate = [
            'categoria_produto_deliveries', 'tributacaos', 'natureza_operacaos'
        ];
        $util = new Util();
        $validateEntry = $util->validateEntry($dataValidate, $request->empresa_id);
        if ($validateEntry != null) {
            session()->flash("flash_erro", $validateEntry['message']);
            return redirect($validateEntry['route']);
        }
        $tributacao = Tributacao::where('empresa_id', request()->empresa_id)
            ->first();
        $categorias = Categoria::where('empresa_id', request()->empresa_id)->get();
        $categoriasDelivery = CategoriaProdutoDelivery::where('empresa_id', request()->empresa_id)->get();
        $divisoes = DivisaoGrade::where('sub_divisao', false)->get();
        $subDivisoes = DivisaoGrade::where('empresa_id', request()->empresa_id)->where('sub_divisao', true)->get();
        return view('produtos_delivery.create', compact('categoriasDelivery', 'categorias', 'tributacao', 'divisoes', 'subDivisoes'));
    }

    public function store(Request $request)
    {
        $this->__validate($request);
        try {
            $request->merge([
                'referencia' => $request->referencia ?? '',
                'categoria_id' => $request->categoriaDelivery_id,
                'valor_anterior' => $request->valor_anterior ?? '',
                'descricao_curta' => $request->descricao_curta ?? '',
                'descricao' => $request->descricao ?? '',
                'valor' => $request->valor ?? ''
            ]);
            $item = ProdutoDelivery::create($request->all());

            $file_name = '';
            if ($request->hasFile('image')) {
                $file_name = $this->util->uploadImage($request, '/produtoDelivery');
                ImagensProdutoDelivery::create([
                    'produto_id' => $item->id,
                    'path' => $file_name
                ]);
            }

            if ($request->tamanho_id) {
                for ($i = 0; $i < sizeof($request->tamanho_id); $i++) {
                    ProdutoPizza::create([
                        'produto_id' => $item->id,
                        'tamanho_id' => $request->tamanho_id[$i],
                        'valor' => $request->valor_pizza[$i]
                    ]);
                }
            }

            session()->flash('flash_sucesso', 'Cadastro com sucesso!');
        } catch (\Exception $e) {
            session()->flash('flash_erro', 'Algo deu errado: ' . $e->getMessage());
            __saveLogError($e, request()->empresa_id);
        }
        return redirect()->route('produtoDelivery.index');
    }

    public function edit($id)
    {
        $item = ProdutoDelivery::findOrFail($id);
        if (!__valida_objeto($item)) {
            abort(403);
        }
        $categoriasDelivery = CategoriaProdutoDelivery::where('empresa_id', request()->empresa_id)->get();
        $categorias = Categoria::where('empresa_id', request()->empresa_id)->get();
        $divisoes = DivisaoGrade::where('sub_divisao', false)->get();
        $subDivisoes = DivisaoGrade::where('empresa_id', request()->empresa_id)->where('sub_divisao', true)->get();
        return view('produtos_delivery.edit', compact('item', 'categoriasDelivery', 'categorias', 'divisoes', 'subDivisoes'));
    }

    public function update(Request $request, $id)
    {
        $this->__validate($request);
        $item = ProdutoDelivery::findOrFail($id);
        try {
            DB::transaction(function () use ($request, $item) {
                $request->merge([
                    'referencia' => $request->referencia ?? '',
                    'categoria_id' => $request->categoriaDelivery_id,
                    'valor_anterior' => $request->valor_anterior ?? '',
                    'descricao_curta' => $request->descricao_curta ?? '',
                    'descricao' => $request->descricao ?? ''
                ]);
                $item->fill($request->all())->save();
            });
            session()->flash("flash_sucesso", "Produto atualizado!");
        } catch (\Exception $e) {
            session()->flash("flash_erro", "Algo deu errado: " . $e->getMessage());
            __saveLogError($e, request()->empresa_id);
        }
        return redirect()->route('produtoDelivery.index');
    }

    private function __validate(Request $request)
    {
        $rules = [
            'produto_id' => 'required',
            // 'valor' => 'required',
            'limite_diario' => 'required',
            'categoriaDelivery_id' => 'required'
        ];
        $messages = [
            'produto_id.required' => 'Campo obrigatório',
            // 'valor.required' => 'Campo obrigatório',
            'limite_diario.required' => 'Campo obrigatório',
            'categoriaDelivery_id.required' => 'Campo obrigatório'
        ];

        $this->validate($request, $rules, $messages);
    }

    public function destroy($id)
    {
        $item = ProdutoDelivery::findOrFail($id);
        if (!__valida_objeto($item)) {
            abort(403);
        }
        try {
            $item->delete();
            session()->flash("flash_sucesso", "Produto deletado!");
        } catch (\Exception $e) {
            session()->flash("flash_erro", "Algo deu errado: " . $e->getMessage());
            __saveLogError($e, request()->empresa_id);
        }
        return redirect()->route('produtoDelivery.index');
    }

    public function push($id)
    {
        return view('produtos_delivery.push');
    }

    public function galeria($id)
    {
        $item = ProdutoDelivery::findOrFail($id);
        if (!__valida_objeto($item)) {
            abort(403);
        }
        return view('produtos_delivery.galery', compact('item'));
    }

    public function saveImagem(Request $request)
    {
        $file_name = '';
        if ($request->hasFile('image')) {
            $file_name = $this->util->uploadImage($request, '/produtoDelivery');
        }
        $request->merge([
            'path' => $file_name
        ]);
        $result = ImagensProdutoDelivery::create($request->all());
        if ($result) {
            session()->flash("flash_sucesso", "Imagem cadastrada com sucesso!");
        } else {
            session()->flash('flash_erro', 'Erro ao cadastrar produto!');
        }
        return redirect()->route('produtoDelivery.galeria', [$request->produto_id]);
    }

    public function deleteImagem($id)
    {
        $item = ImagensProdutoDelivery::findOrFail($id);
        if (!__valida_objeto($item)) {
            abort(403);
        }
        try {
            $this->util->unlinkImage($item, 'produtoDelivery', 'path');
            $item->delete();
            session()->flash('flash_sucesso', 'Deletado com sucesso!');
        } catch (\Exception $e) {
            session()->flash('flash_erro', 'Algo deu errado: ' . $e->getMessage());
            __saveLogError($e, request()->empresa_id);
        }
        return redirect()->back();
    }
}
