<?php

namespace App\Http\Controllers;

use App\Models\ConfigNota;
use App\Models\BannerPromocionalModel;
use Illuminate\Http\Request;
use App\Utils\UploadUtil;


class BannerPromocionalController extends Controller
{
    public function index(Request $request)
    {
        $config = ConfigNota::where('empresa_id', $request->empresa_id)->first();

        if (!$config) {
            return response()->json(['data' => []]);
        }

        $userId = $config->user_id;

        $query = BannerPromocionalModel::where('user_id', $userId);

        // Filtrar banners por nome se o parâmetro 'nome' estiver presente
        if ($request->has('nome') && $request->nome) {
            $query->where('img_banner', 'LIKE', "%{$request->nome}%");
        }

        // Paginação com limite configurado no .env
        $data = $query->paginate(env("PAGINACAO", 10));

        return view('banner_promocao.index', compact('data'));
    }

    public function create(Request $request)
    {
        $config = ConfigNota::where('empresa_id', $request->empresa_id)->first();

        if (!$config) {
            return response()->json(['data' => []]);
        }

        // Buscar o último banner cadastrado
        $lastBanner = BannerPromocionalModel::latest()->first();

        return view('banner_promocao.create', compact('lastBanner'));
    }

    public function store(Request $request)
    {
        try {
            $config = ConfigNota::where('empresa_id', $request->empresa_id)->first();

            if (!$config) {
                return response()->json(['data' => []]);
            }

            // Recupera o user_id a partir da configuração
            $userId = $config->user_id;
            $data = $request->all();
            $data['user_id'] = $userId;

            // Validação do arquivo de imagem
            if ($request->hasFile('img_banner') && $request->file('img_banner')->isValid()) {
                $request->validate([
                    'img_banner' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
                ]);

                $image = $request->file('img_banner');
                $imageName = uniqid() . '.' . $image->getClientOriginalExtension();
                $destinationPath = public_path('uploads/banners');

                if (!file_exists($destinationPath)) {
                    mkdir($destinationPath, 0777, true);
                }

                $image->move($destinationPath, $imageName);
                $data['img_banner'] = 'uploads/banners/' . $imageName;
            }

            // Criação do novo banner
            BannerPromocionalModel::create($data);

            session()->flash('flash_sucesso', 'Cadastrado com sucesso!');
        } catch (\Exception $e) {
            session()->flash('flash_erro', 'Algo deu errado: ' . $e->getMessage());
            __saveLogError($e, $request->empresa_id);
        }

        return redirect()->route('banner_promocao.index');
    }

    public function edit($id, Request $request)
    {
        $config = ConfigNota::where('empresa_id', $request->empresa_id)->first();

        if (!$config) {
            return response()->json(['data' => []]);
        }

        // Encontra o banner pelo ID
        $item = BannerPromocionalModel::findOrFail($id);

        return view('banner_promocao.edit', compact('item'));
    }

    public function update(Request $request, $id)
    {
        $config = ConfigNota::where('empresa_id', $request->empresa_id)->first();

        if (!$config) {
            return response()->json(['data' => []]);
        }

        $userId = $config->user_id;
        $item = BannerPromocionalModel::findOrFail($id);

        try {
            $request->merge(['user_id' => $userId]);

            // Verifica e faz upload da nova imagem
            if ($request->hasFile('img_banner')) {
                if ($item->img_banner && file_exists(public_path('uploads/banners/' . $item->img_banner))) {
                    unlink(public_path('uploads/banners/' . $item->img_banner)); // Deleta a imagem anterior
                }

                // Alterando o caminho para o novo diretório
                $image = $request->file('img_banner');
                $imageName = uniqid() . '.' . $image->getClientOriginalExtension();
                $destinationPath = public_path('uploads/banners');

                if (!file_exists($destinationPath)) {
                    mkdir($destinationPath, 0777, true);
                }

                $image->move($destinationPath, $imageName);
                $request->merge(['img_banner' => 'uploads/banners/' . $imageName]);
            }

            // Atualiza os dados do banner
            $item->fill($request->all())->save();

            session()->flash('flash_sucesso', 'Atualizado com sucesso!');
        } catch (\Exception $e) {
            session()->flash('flash_erro', 'Algo deu errado: ' . $e->getMessage());
            __saveLogError($e, $request->empresa_id);
        }

        return redirect()->route('banner_promocao.index');
    }

    public function destroy($id, Request $request)
    {
        $config = ConfigNota::where('empresa_id', $request->empresa_id)->first();

        if (!$config) {
            return response()->json(['data' => []]);
        }

        $item = BannerPromocionalModel::findOrFail($id);

        try {
            // Remover a imagem do banner antes de deletar
            if ($item->img_banner && file_exists(public_path('uploads/banners/' . $item->img_banner))) {
                unlink(public_path('uploads/banners/' . $item->img_banner));
            }

            // Deletando o banner
            $item->delete();

            session()->flash('flash_sucesso', 'Deletado com sucesso!');
        } catch (\Exception $e) {
            session()->flash('flash_erro', 'Algo deu errado: ' . $e->getMessage());
            __saveLogError($e, $request->empresa_id);
        }

        return redirect()->route('banner_promocao.index');
    }
}
