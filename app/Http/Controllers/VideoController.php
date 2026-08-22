<?php

namespace App\Http\Controllers;

use App\Models\VideoAjuda;
use Illuminate\Http\Request;

class VideoController extends Controller
{
    public function index()
    {
        $data = VideoAjuda::all();
        return view('videos.index', compact('data'));
    }
    
     public function player()
{
        $player = VideoAjuda::all();
    return view('videos.player', compact('player'));
}

    public function create()
    {
        return view('videos.create');
    }

    public function store(Request $request)
    {
        try {
            VideoAjuda::create($request->all());
            session()->flash('flash_sucesso', 'Cadastro com sucesso!');
        } catch (\Exception $e) {
            session()->flash('flash_erro', 'Algo deu errado' . $e->getMessage());
        }
        return redirect()->route('videos.index');
    }
    public function show($id)
{
        $player = VideoAjuda::all();
    return view('videos.video', compact('player'));
}

    public function edit($id)
    {
        $item = VideoAjuda::findOrFail($id);
        return view('videos.edit', compact('item'));
    }

    public function update(Request $request, $id)
    {
        $item = VideoAjuda::findOrFail($id);
        try {
            $item->fill($request->all())->save();
            session()->flash('flash_sucesso', 'Alterado com sucesso!');
        } catch (\Exception $e) {
            session()->flash('flash_erro', 'Algo deu errado' . $e->getMessage());
        }
        return redirect()->route('videos.index');
    }

    public function destroy($id)
    {
        $item = VideoAjuda::findOrFail($id);
        try {
            $item->delete();
            session()->flash('flash_sucesso', 'Apagado com sucesso!');
        } catch (\Exception $e) {
            session()->flash('flash_erro', 'Algo deu errado' . $e->getMessage());
        }
        return redirect()->route('videos.index');
    }
}
