<?php

namespace App\Http\Controllers;

use App\Models\ContatoEcommerce;
use Illuminate\Http\Request;

class ContatoEcommerceController extends Controller
{
    public function index(Request $request)
    {
        $data = ContatoEcommerce::where('empresa_id', $request->empresa_id)
        ->paginate(env('PAGINACAO'));
        return view('contato_ecommerce.index', compact('data'));
    }
}
