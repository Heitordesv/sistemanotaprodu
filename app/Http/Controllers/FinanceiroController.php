<?php

namespace App\Http\Controllers;

use App\Models\Empresa;
use App\Models\Payment;
use Illuminate\Http\Request;

class FinanceiroController extends Controller
{
    public function index(Request $request)
    {
        $start_date = $request->get('start_date');
        $end_date = $request->get('end_date');
        $type_search = $request->get('tipo');
        $estado = $request->get('estado');
        $data = Payment::where('empresa_id', $request->empresa_id)
        ->when(!empty($request->nome), function ($query) use ($request) {
            return $query->whereDate('nome', '=', $request->nome);
        })
        ->when(!empty($start_date), function ($query) use ($start_date, $estado) {
            return $query->whereDate($estado, '<=', $start_date);
        })
        ->when(!empty($end_date), function ($query) use ($end_date, $estado) {
            return $query->whereDate($estado, '<=', $end_date);
        })
        ->when(!empty($start_date), function ($query) use ($start_date, $type_search) {
            return $query->whereDate($type_search, '<=', $start_date);
        })
        ->when(!empty($end_date), function ($query) use ($end_date, $type_search) {
            return $query->whereDate($type_search, '<=', $end_date);
        })
        ->when($estado != "", function ($query) use ($estado) {
            return $query->where('estado', $estado);
        })
        ->orderBy('data_registro', 'asc')
        ->paginate(env("PAGINACAO"));

        return view('financeiro.index', compact('data'));
    }

    public function list()
    {
        $data = Payment::all();
        return view('financeiro.list', compact('data'));
    }
}
