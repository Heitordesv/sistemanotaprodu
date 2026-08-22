<?php

namespace App\Http\Controllers;

use App\Models\ClienteEcommerce;
use Illuminate\Http\Request;

class EnderecosEcommerceController extends Controller
{
    public function index($id)
    {   
        $cliente = ClienteEcommerce::findOrFail($id);
        return view('enderecos_ecommerce.index', compact('cliente'));
    }
}
