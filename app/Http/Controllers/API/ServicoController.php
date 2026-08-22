<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Servico;
use Illuminate\Http\Request;

class ServicoController extends Controller
{
    public function find($id){
        $item = Servico::findOrFail($id);
        return response()->json($item, 200);
    }
}
