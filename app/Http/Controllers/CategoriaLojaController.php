<?php

namespace App\Http\Controllers;

use App\Models\CategoriaMasterDelivery;
use Illuminate\Http\Request;

class CategoriaLojaController extends Controller
{
    public function index()
    {
        $data = CategoriaMasterDelivery::where('empresa_id', request()->empresa_id);

        return view('categoria_delivery.index', compact('data'));
    }
}
