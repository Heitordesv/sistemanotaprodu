<?php

namespace App\Http\Controllers\Pdv;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AberturaCaixa;
use App\Models\Venda;
use App\Models\VendaCaixa;

class CaixaController extends Controller
{
    public function index($usuario_id){
        $ab = AberturaCaixa::where('ultima_venda_nfce', 0)
        ->where('status', 0)
        ->where('usuario_id', $usuario_id)
        ->orderBy('id', 'desc')->first();

        $ab2 = AberturaCaixa::where('ultima_venda_nfe', 0)
        ->where('status', 0)
        ->where('usuario_id', $usuario_id)
        ->orderBy('id', 'desc')->first();

        if($ab != null && $ab2 == null){
            $ab->status = $ab->status == 0 ? false : true;
            return response()->json($ab, 200);
        }else if($ab == null && $ab2 != null){
            $ab2->status = $ab2->status == 0 ? false : true;
            return response()->json($ab2, 200);
        }else if($ab != null && $ab2 != null){
            if(strtotime($ab->created_at) > strtotime($ab2->created_at)){
                $ab->status = $ab->status == 0 ? false : true;
                return response()->json($ab, 200);
            }else{
                $ab2->status = $ab2->status == 0 ? false : true;
                return response()->json($ab2, 200);
            }
        }else{
            return response()->json(null, 401);
        }
    }

    public function abrir(Request $request){
        try{
            $ultimaVendaNfce = VendaCaixa::
            where('empresa_id', $request->empresa_id)
            ->orderBy('id', 'desc')->first();

            $ultimaVendaNfe = Venda::
            where('empresa_id', $request->empresa_id)
            ->orderBy('id', 'desc')->first();

            $result = AberturaCaixa::create([
                'usuario_id' => $request->usuario,
                'valor' => str_replace(",", ".", $request->valor),
                'empresa_id' => $request->empresa_id,
                'primeira_venda_nfe' => $ultimaVendaNfe != null ? 
                $ultimaVendaNfe->id : 0,
                'primeira_venda_nfce' => $ultimaVendaNfce != null ? 
                $ultimaVendaNfce->id : 0,
                'status' => 0
            ]);
            return response()->json($result, 200);
        }catch(\Exception $e){
            return response()->json($e->getMessage(), 401);
        }

    }
}
