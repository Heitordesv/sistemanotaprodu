<?php

namespace App\Http\Controllers;

use App\Models\DeliveryConfig;
use Illuminate\Http\Request;

class LojaController extends Controller
{
    public function index()
    {
        $data = DeliveryConfig::orderBy('id', 'desc')->get();
        return view('lojas.index', compact('data'));
    }

    public function alterarStatus($id){
        $item = DeliveryConfig::find($id);

        $item->status = !$item->status;
        $item->save();
        session()->flash("flash_sucesso", "Status alterado!");

        return redirect()->back();
    }
}
