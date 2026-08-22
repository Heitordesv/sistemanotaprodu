<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Produto;
use App\Models\ConfigNota;
use App\Models\Tributacao;
use App\Models\Categoria;
use App\Models\DivisaoGrade;
use App\Models\NaturezaOperacao;
use Illuminate\Support\Str;
use App\Helpers\StockMove;

class NuvemShopProdutoController extends Controller
{
    public function index(Request $request){
        $page = $request->page ? $request->page : 1;
        $search = $request->search;
        $store_info = session('store_info');
        if(!$store_info){
            return redirect()->route('nuvemshop-auth.authorize');
        }
        $api = new \TiendaNube\API($store_info['store_id'], $store_info['access_token'], 'Awesome App ('.$store_info['email'].')');

        if($search != ""){
            $produtos = (array)$api->get("products?q='".$search."'&per_page=21");
        }else{
            $produtos = (array)$api->get("products?page=".$page."&per_page=12");
        }
        $produtos = $produtos['body'];

        $this->validaProdutos($produtos);
        
        return view('nuvemshop_produtos.index', compact('produtos', 'page', 'search'));
    }

    private function validaProdutos($produtos){
        foreach($produtos as $p){
            // echo "<pre>";
            // print_r($p);
            // echo "</pre>";
            $rand = Str::random(20);

            if(sizeof($p->variants) > 1){
                foreach($p->variants as $v){
                    $ean = $v->barcode;
                    $result = Produto::
                    where('codBarras', $ean)
                    ->where('codBarras', '!=', 'SEM GTIN')
                    ->where('empresa_id', request()->empresa_id)
                    ->first();

                    // echo "<pre>";
                    // print_r($v);
                    // echo "</pre>";
                    // die;

                    if($result == null){
                        $str = "";
                        foreach($v->values as $s){
                            $str .= $s->pt . " ";
                        }
                        $result = Produto::
                        where('nome', $p->name->pt)
                        ->where('str_grade', $str)
                        ->where('empresa_id', request()->empresa_id)
                        ->first();

                    }

                    if($result == null){
                        $this->salvarProdutoBanco2($p, $v, $rand, $str);
                    }
                }

            }else{

                $result = Produto::
                where('nome', $p->name->pt)
                ->where('empresa_id', request()->empresa_id)
                ->first();

                if($result == null){
                    $ean = $p->variants[0]->barcode;
                    $result = Produto::
                    where('codBarras', $ean)
                    ->where('codBarras', '!=', 'SEM GTIN')
                    ->where('empresa_id', request()->empresa_id)
                    ->first();
                }

                if($result == null){
                //cadastrar
                    $this->salvarProdutoBanco2($p, null, $rand);
                }else{
                    $result->nuvemshop_id = $p->id;
                    $result->nuvemshop_id;
                    $result->save();
                }
            }
        }
    }

    public function salvarProdutoBanco2($prod, $variacao = null, $rand, $str_grade = ""){

        $config = ConfigNota::where('empresa_id', request()->empresa_id)->first();
        $natureza = Produto::firstNatureza(request()->empresa_id);
        if($natureza == null){
            session()->flash("flash_warning", "Cadastre uma natureza de operação!");
            return redirect()->route('naturezas.index');
        }
        $tributacao = Tributacao::where('empresa_id', request()->empresa_id)->first();
        if($tributacao == null){
            session()->flash("flash_warning", "Cadastre uma tributação!");
            return redirect()->route('tributos.index');
        }
        $categoria = Categoria::where('empresa_id', request()->empresa_id)->first();
        if($categoria == null){
            session()->flash("flash_warning", "Cadastre uma categoria!");
            return redirect()->route('categorias.index');
        }
        $valorVenda = (float)__convert_value_bd($prod->variants[0]->price);

        $valorCompra = $valorVenda - (($valorVenda*$config->percentual_lucro_padrao)/100);
        $data = [
            'nome' => $prod->name->pt,
            'categoria_id' => $categoria->id,
            'cor' => '',
            'valor_venda' => $valorVenda,
            'NCM' => $tributacao->ncm_padrao,
            'CST_CSOSN' => $config->CST_CSOSN_padrao,
            'CST_PIS' => $config->CST_PIS_padrao,
            'CST_COFINS' => $config->CST_COFINS_padrao,
            'CST_IPI' => $config->CST_IPI_padrao,
            'unidade_compra' => 'UN',
            'unidade_venda' => 'UN',
            'composto' => 0,
            'conversao_unitaria' => 1,
            'valor_livre' => 0,
            'perc_icms' => $tributacao->icms,
            'perc_pis' => $tributacao->pis,
            'perc_cofins' => $tributacao->cofins,
            'perc_ipi' => $tributacao->ipi,
            'CFOP_saida_estadual' => $natureza->CFOP_saida_estadual,
            'CFOP_saida_inter_estadual' => $natureza->CFOP_saida_inter_estadual,
            'codigo_anp' => '',
            'descricao_anp' => '',
            'perc_iss' => 0,
            'cListServ' => '',
            'imagem' => '',
            'alerta_vencimento' => 0,
            'valor_compra' => $valorCompra,
            'gerenciar_estoque' => 0,
            'estoque_minimo' => 0,
            'referencia' => '',
            'tela_id' => NULL,
            'empresa_id' => request()->empresa_id,
            'percentual_lucro' => $config->percentual_lucro_padrao,
            'referencia_grade' => $rand,
            "nuvemshop_id" => $prod->id
        ];

        if($variacao == null){
            $data['codBarras'] = $prod->variants[0]->barcode ?? '';
            $data['largura'] = $prod->variants[0]->width ?? '';
            $data['comprimento'] = $prod->variants[0]->depth ?? '';
            $data['altura'] = $prod->variants[0]->height ?? '';
            $data['peso_liquido'] = $prod->variants[0]->weight ?? '';
            $data['peso_bruto'] = $prod->variants[0]->weight ?? '';
        }else{
            $data['codBarras'] = $variacao->barcode ?? '';
            $data['largura'] = $variacao->width ?? '';
            $data['comprimento'] = $variacao->depth ?? '';
            $data['altura'] = $variacao->height ?? '';
            $data['peso_liquido'] = $variacao->weight ?? '';
            $data['peso_bruto'] = $variacao->weight ?? '';
            $data['str_grade'] = $str_grade;
            $data['grade'] = 1;

        }
        $produto = Produto::create($data);

        if($prod->variants[0]->stock){
            $stockMove = new StockMove();
            $stockMove->pluStock($produto->id, __convert_value_bd($prod->variants[0]->stock), $valorCompra);
        }
    }

    private function salvarProdutoBanco($request, $nuvemshop_id){

        if($request->produto_id == 0){
            $config = ConfigNota::where('empresa_id', request()->empresa_id)->first();
            $natureza = Produto::firstNatureza(request()->empresa_id);
            $tributacao = Tributacao::where('empresa_id', request()->empresa_id)->first();
            $categoria = Categoria::where('empresa_id', request()->empresa_id)->first();
            $valorVenda = __convert_value_bd($request->valor);

            $valorCompra = $valorVenda - (($valorVenda*$config->percentual_lucro_padrao)/100);

            $arr = [
                'nome' => $request->nome,
                'categoria_id' => $categoria->id,
                'cor' => '',
                'valor_venda' => $valorVenda,
                'NCM' => $tributacao->ncm_padrao,
                'CST_CSOSN' => $config->CST_CSOSN_padrao,
                'CST_PIS' => $config->CST_PIS_padrao,
                'CST_COFINS' => $config->CST_COFINS_padrao,
                'CST_IPI' => $config->CST_IPI_padrao,
                'unidade_compra' => 'UN',
                'unidade_venda' => 'UN',
                'composto' => 0,
                'codBarras' => 'SEM GTIN',
                'conversao_unitaria' => 1,
                'valor_livre' => 0,
                'perc_icms' => $tributacao->icms,
                'perc_pis' => $tributacao->pis,
                'perc_cofins' => $tributacao->cofins,
                'perc_ipi' => $tributacao->ipi,
                'CFOP_saida_estadual' => $natureza->CFOP_saida_estadual,
                'CFOP_saida_inter_estadual' => $natureza->CFOP_saida_inter_estadual,
                'codigo_anp' => '',
                'descricao_anp' => '',
                'perc_iss' => 0,
                'cListServ' => '',
                'imagem' => '',
                'alerta_vencimento' => 0,
                'valor_compra' => $valorCompra,
                'gerenciar_estoque' => 0,
                'estoque_minimo' => 0,
                'referencia' => $request->referencia,
                'tela_id' => NULL,
                'largura' => $largura,
                'comprimento' => $comprimento,
                'altura' => $altura,
                'peso_liquido' => $peso,
                'peso_bruto' => $peso,
                'empresa_id' => request()->empresa_id,
                'percentual_lucro' => $config->percentual_lucro_padrao,
                'referencia_grade' => Str::random(20),
                "nuvemshop_id" => $nuvemshop_id
            ];

            $produto = Produto::create($arr);

            if($request->estoque){
                $stockMove = new StockMove();
                $stockMove->pluStock($produto->id, __convert_value_bd($request->estoque), $valorCompra);
            }
        }else{
            $produto = Produto::find($request->produto_id);
            $produto->nuvemshop_id = $nuvemshop_id;
            $produto->save();
        }
    }

    private function _validate(Request $request){
        $rules = [
            'referencia' => 'required',
            'nome' => 'required',
            'descricao' => 'required',
            'valor' => 'required',
        ];

        $messages = [
            'referencia.required' => 'O campo referência é obrigatório.',
            'descricao.required' => 'O campo descricao é obrigatório.',
            'nome.required' => 'O campo nome é obrigatório.',
            'valor.required' => 'O campo valor é obrigatório.',
            'estoque.required' => 'O campo estoque é obrigatório.'
        ];
        $this->validate($request, $rules, $messages);
    }

    public function produto_galeria($id){
        $store_info = session('store_info');
        $api = new \TiendaNube\API($store_info['store_id'], $store_info['access_token'], 'Awesome App ('.$store_info['email'].')');
        $produto = (array)$api->get("products/".$id);
        $produto = $produto['body'];

        $prodBd = Produto::where('nuvemshop_id', $produto->id)
        ->first();

        return view('nuvemshop_produtos.galery', compact('produto', 'prodBd'));

    }

    public function delete_imagem($produto_id, $image_id){

        $store_info = session('store_info');
        $api = new \TiendaNube\API($store_info['store_id'], $store_info['access_token'], 'Awesome App ('.$store_info['email'].')');
        try{
            $response = $api->delete("products/$produto_id/images/$image_id");
            session()->flash("flash_sucesso", "Imagem removida!");

        }catch(\Exception $e){
            session()->flash("flash_erro", "Erro inesperado: " . $e->getMessage());

        }
        return redirect()->back();

    }

    public function save_imagem(Request $request){
        if($request->hasFile('file')){
            $store_info = session('store_info');
            $api = new \TiendaNube\API($store_info['store_id'], $store_info['access_token'], 'Awesome App ('.$store_info['email'].')');

            $image = base64_encode(file_get_contents($request->file('file')->path()));

            $ext = $request->file('file')->getClientOriginalExtension();
            $response = $api->post("products/$request->id/images",[
                "filename" => Str::random(20).".".$ext,
                "attachment" => $image
            ]);

            session()->flash("flash_sucesso", "Imagem salva!");
        }else{
            session()->flash("flash_erro", "Selecione uma imagem!");
        }

        return redirect()->back();
    }

    public function create(){
        $store_info = session('store_info');
        $api = new \TiendaNube\API($store_info['store_id'], $store_info['access_token'], 'Awesome App ('.$store_info['email'].')');

        // echo "<pre>";
        // print_r($produto);
        // echo "</pre>";

        // die;

        $categorias = (array)$api->get("categories");
        $categoriasNuvemShop = $categorias['body'];
        $categorias = Categoria::where('empresa_id', request()->empresa_id)->get();
        $naturezaPadrao = NaturezaOperacao::where('empresa_id', request()->empresa_id)
        ->first();

        $divisoes = DivisaoGrade::where('empresa_id', request()->empresa_id)
        ->where('sub_divisao', false)
        ->get();

        $subDivisoes = DivisaoGrade::where('empresa_id', request()->empresa_id)
        ->where('sub_divisao', true)
        ->get();
        $tributacao = Tributacao::where('empresa_id', request()->empresa_id)->first();
        return view('nuvemshop_produtos.create', 
            compact('categorias', 'categoriasNuvemShop', 'naturezaPadrao', 'divisoes', 'subDivisoes', 
                'tributacao'));
    }

    public function edit($id){
        $store_info = session('store_info');
        $api = new \TiendaNube\API($store_info['store_id'], $store_info['access_token'], 'Awesome App ('.$store_info['email'].')');
        $produto = (array)$api->get("products/".$id);
        $produto = $produto['body'];

        $categorias = (array)$api->get("categories");
        $categoriasNuvemShop = $categorias['body'];

        $item = Produto::where('nuvemshop_id', $id)
        ->first();

        if($item == null){
            session()->flash("flash_erro", "Produto não encontrado na base de dados local!");
            return redirect()->back();
        }

        $categorias = Categoria::where('empresa_id', request()->empresa_id)->get();
        $naturezaPadrao = NaturezaOperacao::where('empresa_id', request()->empresa_id)
        ->first();

        $divisoes = DivisaoGrade::where('empresa_id', request()->empresa_id)
        ->where('sub_divisao', false)
        ->get();

        $subDivisoes = DivisaoGrade::where('empresa_id', request()->empresa_id)
        ->where('sub_divisao', true)
        ->get();
        $tributacao = Tributacao::where('empresa_id', request()->empresa_id)->first();
        return view('nuvemshop_produtos.edit', compact('categorias', 'item', 'categoriasNuvemShop', 'naturezaPadrao', 'divisoes', 'subDivisoes', 'tributacao', 'produto'));

    }

    public function store(Request $request){

        $nome = $request->nome;
        $descricao = $request->descricao;
        $valor = $request->valor;
        $id = $request->id;
        $categoria_id = $request->categoria_id;
        $estoque = $request->estoque;
        $valor_promocional = $request->valor_promocional ?? 0;
        $codigo_barras = $request->codigo_barras ?? '';

        $peso = $request->peso ? __convert_value_bd($request->peso) : 0;
        $largura = $request->largura ? __convert_value_bd($request->largura) : 0;
        $altura = $request->altura ? __convert_value_bd($request->altura) : 0;
        $comprimento = $request->comprimento ? __convert_value_bd($request->comprimento) : 0;
        $this->_validate($request);

        try{
            $store_info = session('store_info');
            $api = new \TiendaNube\API($store_info['store_id'], $store_info['access_token'], 'Awesome App ('.$store_info['email'].')');

            $response = $api->post("products", [
                'name' => $nome,
                'parent' => $categoria_id,
                'description' => $descricao
            ]);

            $produto = $response->body;

            $resp = $response = $api->put("products/$produto->id/variants/".$produto->variants[0]->id, [
                'price' => __convert_value_bd($valor),
                'stock' => __convert_value_bd($estoque),
                'promotional_price' => __convert_value_bd($valor_promocional),
                'barcode' => __convert_value_bd($codigo_barras),
                "weight" => $peso,
                "width" => $largura,
                "height" => $altura,
                "depth" => $comprimento,
            ]);

            $p = Produto::findOrFail($request->referencia);
            $p->nuvemshop_id = $produto->id;
            $p->save();
            if($response){
                session()->flash("flash_sucesso", "Produto criado!");
            }else{
                session()->flash("flash_erro", "Erro inesperado: " . $e->getMessage());
            }
        }catch(\Exception $e){
            // echo $e->getMessage();
            // die;
            session()->flash("flash_erro", "Erro inesperado: " . $e->getMessage());

        }
        return redirect()->route('nuvemshop-produtos.index');

    }

    public function update(Request $request, $id){
        try{
            $item = Produto::findOrFail($id);
            $nome = $request->nome;
            $descricao = $request->descricao;
            $valor = $request->valor;
            $id = $request->id;
            $categoria_id = $request->categoria_id;
            $estoque = $request->estoque;
            $valor_promocional = $request->valor_promocional ?? 0;
            $codigo_barras = $request->codigo_barras ?? '';

            $peso = $request->peso ? __convert_value_bd($request->peso) : 0;
            $largura = $request->largura ? __convert_value_bd($request->largura) : 0;
            $altura = $request->altura ? __convert_value_bd($request->altura) : 0;
            $comprimento = $request->comprimento ? __convert_value_bd($request->comprimento) : 0;
            $store_info = session('store_info');
            $api = new \TiendaNube\API($store_info['store_id'], $store_info['access_token'], 'Awesome App ('.$store_info['email'].')');

            $response = $api->put("products/$item->nuvemshop_id", [
                'name' => $nome,
                'description' => $descricao,
                'categories' => $categoria_id ? [$categoria_id] : []
            ]);

            $produto = (array)$api->get("products/".$item->nuvemshop_id);
            $produto = $produto['body'];

            if(sizeof($produto->variants) == 1){

                $resp = $response = $api->put("products/$item->nuvemshop_id/variants/".$produto->variants[0]->id, [
                    'price' => __convert_value_bd($valor),
                    'stock' => __convert_value_bd($estoque),
                    'promotional_price' => __convert_value_bd($valor_promocional),
                    'barcode' => __convert_value_bd($codigo_barras),

                    "weight" => $peso,
                    "width" => $largura,
                    "height" => $altura,
                    "depth" => $comprimento,
                ]);
            }

            session()->flash("flash_sucesso", "Produto atualizado!");

        }catch(\Exception $e){
            echo $e->getMessage();
            die;
            session()->flash("flash_erro", "Erro inesperado: " . $e->getMessage());

        }
        return redirect()->route('nuvemshop-produtos.index');
    }

    public function destroy($id){

        try{
            $store_info = session('store_info');
            $api = new \TiendaNube\API($store_info['store_id'], $store_info['access_token'], 'Awesome App ('.$store_info['email'].')');
        }catch(\Exception $e){

            session()->flash("flash_erro", "Erro inesperado: " . $e->getMessage());

        }
    }

    public function galery($id){
        $store_info = session('store_info');
        $api = new \TiendaNube\API($store_info['store_id'], $store_info['access_token'], 'Awesome App ('.$store_info['email'].')');
        $produto = (array)$api->get("products/".$id);
        $produto = $produto['body'];

        $prodBd = Produto::where('nuvemshop_id', $id)
        ->first();

        return view('nuvemshop_produtos.galery', compact('produto', 'prodBd'));

    }

    public function saveImage(Request $request, $id){
        $item = Produto::findOrFail($id);
        if($request->hasFile('image')){
            $store_info = session('store_info');
            $api = new \TiendaNube\API($store_info['store_id'], $store_info['access_token'], 'Awesome App ('.$store_info['email'].')');

            $image = base64_encode(file_get_contents($request->file('image')->path()));

            $ext = $request->file('image')->getClientOriginalExtension();
            $response = $api->post("products/$item->nuvemshop_id/images",[
                "filename" => Str::random(20).".".$ext,
                "attachment" => $image
            ]);

            session()->flash("flash_sucesso", "Imagem salva!");
        }else{
            session()->flash("flash_erro", "Selecione uma imagem!");
        }

        return redirect()->back();
    }

    public function destroyImage($image_id){
        $produto_id = request()->produto_id;

        $store_info = session('store_info');
        $api = new \TiendaNube\API($store_info['store_id'], $store_info['access_token'], 'Awesome App ('.$store_info['email'].')');
        try{
            $response = $api->delete("products/$produto_id/images/$image_id");
            session()->flash("flash_sucesso", "Imagem removida!");

        }catch(\Exception $e){
            session()->flash("flash_erro", "Erro inesperado: " . $e->getMessage());

        }
        return redirect()->back();

    }
}
