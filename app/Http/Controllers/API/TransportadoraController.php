<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Transportadora;
use Illuminate\Http\Request;

class TransportadoraController extends Controller
{
    public function store(Request $request){
        try{
            $item = Transportadora::create($request->all());
            return response()->json($item, 200);

        }catch(\Exception $e){
            return response()->json($e->getMessage(), 400);
        }
    }
}
