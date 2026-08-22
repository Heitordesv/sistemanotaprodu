<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Usuario;
use App\Models\TemaUsuario;

class UsuarioController extends Controller
{
    public function setTheme(Request $request)
    {
        try {
            $item = Usuario::findOrFail($request->usuario_id);
            $tema = $item->theme;

            if ($tema == null) {
                TemaUsuario::create($request->all());
            } else {
                $tema->fill($request->all())->save();
            }


            if (isset($request->tema)) {
                if ($request->tema == 'minimaltheme') {
                    $tema->delete();
                }
            }
            return response()->json($tema, 200);
        } catch (\Exception $e) {
            return response()->json($e->getMessage(), 400);
        }
    }


    public function avisoSonoro(Request $request)
    {
        try{
            $item = Usuario::findOrFail($request->usuario_id);
            $item->aviso_sonoro = $request->aviso_sonoro;
            $item->save();
        }catch(\Exception $e){
            return response()->json($e->getMessage(), 400);
        }
    }
}
