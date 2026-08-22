<?php

namespace App\Http\Controllers;

use App\Models\DivisaoGrade;
use Illuminate\Http\Request;

class DivisaoGradeController extends Controller
{
    public function index()
    {
        $data = DivisaoGrade::where('empresa_id', request()->empresa_id)->get();
        return view('divisao_grade.index', compact('data'));
    }

    public function create()
    {
        return view('divisao_grade.create');
    }

    public function store(Request $request)
    {
        try {
            DivisaoGrade::create($request->all());
            session()->flash("flash_sucesso", "Cadastrado com Sucesso!");
        } catch (\Exception $e) {
            session()->flash("flash_erro", "Algo deu errado" . $e->getMessage());
            __saveLogError($e, request()->empresa_id);
        }
        return redirect()->route("divisaoGrade.index");
    }

    public function edit($id)
    {
        $item = DivisaoGrade::findOrFail($id);
        if (!__valida_objeto($item)) {
            abort(403);
        }
        return view('divisao_grade.edit', compact('item'));
    }

    public function update(Request $request, $id)
    {
        $item = DivisaoGrade::findOrFail($id);
        try {
            $item->fill($request->all())->save();
            session()->flash("flash_sucesso", "Editado com Sucesso!");
        } catch (\Exception $e) {
            session()->flash("flash_erro", "Algo de Errado");
            __saveLogError($e, request()->empresa_id);
        }
        return redirect()->route("divisaoGrade.index");
    }

    public function destroy(Request $request, $id)
    {
        $item = DivisaoGrade::findOrFail($id);
        if (!__valida_objeto($item)) {
            abort(403);
        }
        try {
            $item->delete();
            session()->flash("flash_sucesso", "Editado com Sucesso!");
        } catch (\Exception $e) {
            session()->flash("flash_erro", "Algo de Errado");
            __saveLogError($e, request()->empresa_id);
        }
        return redirect()->route("divisaoGrade.index");
    }
}
