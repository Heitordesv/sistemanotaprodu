<?php

namespace App\Http\Controllers;

use App\Models\InformativoEcommerce;
use Illuminate\Http\Request;

class InformativoController extends Controller
{
    public function index(Request $request)
    {
        $data = InformativoEcommerce::where('empresa_id', $request->empresa_id)
        ->paginate(env('PAGINACAO'));
        return view('informativo_ecommerce.index', compact('data'));
    }
}
