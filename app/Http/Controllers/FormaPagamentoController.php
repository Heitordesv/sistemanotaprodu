<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\FormaPagamento;
use Illuminate\Support\Facades\DB;

class FormaPagamentoController extends Controller
{
    public function index(Request $request)
    {
        $data = FormaPagamento::where('empresa_id', request()->empresa_id)
            ->when(!empty($request->nome), function ($q) use ($request) {
                return  $q->where(function ($quer) use ($request) {
                    return $quer->where('nome', 'LIKE', "%$request->nome%");
                });
            })
            ->paginate(env("PAGINACAO"));
        $tiposNaoDelete = ['a vista', '30_dias', 'conta_crediario', 'personalizado'];
        return view('forma_pagamentos/index', compact('data', 'tiposNaoDelete'));
    }

    public function create()
    {
        $podeEditar = true;
        return view('forma_pagamentos/create', compact('podeEditar'));
    }

    public function store(Request $request)
    {
        $this->_validate($request);
        try {
            $request->merge([
                'taxa' => __convert_value_bd($request->taxa),
                'infos' => $request->infos ?? '',
                'chave' => strtolower($this->criaChave($request->nome))
            ]);
            DB::transaction(function () use ($request) {
                FormaPagamento::create($request->all());
            });
            session()->flash("flash_sucesso", "Forma de pagamento cadastrada!");
        } catch (\Exception $e) {
            session()->flash("flash_erro", "Algo deu errado: " . $e->getMessage());
            __saveLogError($e, request()->empresa_id);
        }
        return redirect()->route('formasPagamento.index');
    }

    private function criaChave($chave)
    {
        return preg_replace(array("/(á|à|ã|â|ä)/", "/(Á|À|Ã|Â|Ä)/", "/(é|è|ê|ë)/", "/(É|È|Ê|Ë)/", "/(í|ì|î|ï)/", "/(Í|Ì|Î|Ï)/", "/(ó|ò|õ|ô|ö)/", "/(Ó|Ò|Õ|Ô|Ö)/", "/(ú|ù|û|ü)/", "/(Ú|Ù|Û|Ü)/", "/(ñ)/", "/(Ñ)/", "/(ç)/"), explode(" ", "a A e E i I o O u U n N c"), $chave);
    }

    public function edit($id)
    {
        $item = FormaPagamento::findOrFail($id);
        if (!__valida_objeto($item)) {
            abort(403);
        }
        $tiposNaoEdit = ['a vista', '30_dias', 'conta_crediario', 'personalizado'];
        $podeEditar = true;
        if (in_array($item->chave, $tiposNaoEdit)) {
            $podeEditar = false;
        }
        return view('forma_pagamentos/edit', compact('item', 'podeEditar'));
    }

    public function update(Request $request, $id)
    {
        $this->_validate($request);
        $item = FormaPagamento::findOrFail($id);
        try {
            $request->merge([
                'taxa' => __convert_value_bd($request->taxa),
                'infos' => $request->infos ?? '',
                'chave' => strtolower($this->criaChave($request->nome))
            ]);
            DB::transaction(function () use ($request, $item) {
                $item->fill($request->all())->save();
            });
            session()->flash("flash_sucesso", "Forma de pagamento atualizada!");
        } catch (\Exception $e) {
            session()->flash("flash_erro", "Algo deu errado: " . $e->getMessage());
            __saveLogError($e, request()->empresa_id);
        }
        return redirect()->route('formasPagamento.index');
    }

    private function _validate(Request $request)
    {
        $rules = [
            'nome' => $request->podeEditar ? 'required|max:40' : '',
            'tipo_taxa' => 'required',
            'taxa' => 'required',
            'infos' => 'max:100',
            'prazo_dias' => $request->podeEditar ? 'required' : ''
        ];
        $messages = [
            'nome.required' => 'O campo nome é obrigatório.',
            'nome.max' => '40 caracteres maximos permitidos.',
            'infos.max' => '100 caracteres maximos permitidos.',
            'tipo_taxa.required' => 'Campo obrigatório.',
            'taxa.required' => 'Campo obrigatório.',
            'prazo_dias.required' => 'Campo obrigatório.',
        ];
        $this->validate($request, $rules, $messages);
    }

    public function destroy(Request $request, $id)
    {
        $item = FormaPagamento::findOrFail($id);
        if (!__valida_objeto($item)) {
            abort(403);
        }
        try {
            $item->delete();
            session()->flash("flash_sucesso", "Forma de pagamento removida!");
        } catch (\Exception $e) {
            session()->flash("flash_erro", "Algo deu errado: " . $e->getMessage());
            __saveLogError($e, request()->empresa_id);
            return redirect()->route('formasPagamento.index');
        }
    }
}
