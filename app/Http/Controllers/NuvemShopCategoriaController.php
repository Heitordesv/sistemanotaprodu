<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NuvemShopCategoriaController extends Controller
{
    public function index(){
        $store_info = session('store_info');

        if(!$store_info){
            return redirect()->route('nuvemshop-auth.authorize');
        }
        $api = new \TiendaNube\API($store_info['store_id'], $store_info['access_token'], 'Awesome App ('.$store_info['email'].')');

        $categorias = (array)$api->get("categories");
        $categorias = $categorias['body'];

        return view('nuvemshop_categorias/index', compact('categorias'));
    }

    public function create(){

        $store_info = session('store_info');
        $api = new \TiendaNube\API($store_info['store_id'], $store_info['access_token'], 'Awesome App ('.$store_info['email'].')');
        $categorias = (array)$api->get("categories");
        $categorias = $categorias['body'];
        return view('nuvemshop_categorias/create', compact('categorias'));
    }

    public function edit($id){

        $store_info = session('store_info');
        $api = new \TiendaNube\API($store_info['store_id'], $store_info['access_token'], 'Awesome App ('.$store_info['email'].')');
        $categorias = (array)$api->get("categories");
        $categorias = $categorias['body'];

        $categoria = (array)$api->get("categories/".$id);
        $item = $categoria['body'];
        return view('nuvemshop_categorias/edit', compact('categorias', 'item'));
    }

    public function store(Request $request){
        $nome = $request->nome;
        $descricao = $request->descricao;
        $categoria_id = $request->categoria_id;

        try{
            $store_info = session('store_info');
            $api = new \TiendaNube\API($store_info['store_id'], $store_info['access_token'], 'Awesome App ('.$store_info['email'].')');

            $response = $api->post("categories", [
                'name' => $nome,
                'parent' => $categoria_id,
                'description' => $descricao
            ]);
            if($response){
                session()->flash("flash_sucesso", "Categoria criada!");
            }else{
                session()->flash("flash_erro", "Erro inesperado: " . $e->getMessage());
            }
        }catch(\Exception $e){
            session()->flash("flash_erro", "Erro inesperado: " . $e->getMessage());

        }
        return redirect()->route('nuvemshop-categoria.index');
    }

    public function update(Request $request, $id){
        $nome = $request->nome;
        $descricao = $request->descricao;
        $categoria_id = $request->categoria_id;

        try{
            $store_info = session('store_info');
            $api = new \TiendaNube\API($store_info['store_id'], $store_info['access_token'], 'Awesome App ('.$store_info['email'].')');

            if($categoria_id == 0){
                $response = $api->put("categories/$id", [
                    'name' => $nome,
                    'description' => $descricao
                ]);
            }else{
                $response = $api->put("categories/$id", [
                    'name' => $nome,
                    'parent' => $categoria_id,
                    'description' => $descricao
                ]);
            }
            if($response){
                session()->flash("flash_sucesso", "Categoria atualizada!");
            }else{
                session()->flash("flash_erro", "Erro inesperado: " . $e->getMessage());
            }
        }catch(\Exception $e){
            session()->flash("flash_erro", "Erro inesperado: " . $e->getMessage());

        }
        return redirect()->route('nuvemshop-categoria.index');
    }

    public function destroy($id){
        $store_info = session('store_info');
        $api = new \TiendaNube\API($store_info['store_id'], $store_info['access_token'], 'Awesome App ('.$store_info['email'].')');
        try{
            $response = $api->delete("categories/$id");
            session()->flash("flash_sucesso", "Categoria removida!");

        }catch(\Exception $e){
            session()->flash("flash_erro", "Erro inesperado: " . $e->getMessage());

        }
        return redirect()->back();
    }
}
