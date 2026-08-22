<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Contigencia;
use App\Models\ConfigNota;
use NFePHP\NFe\Factories\Contingency;

class ContigenciaController extends Controller
{
    public function index(){
        $data = Contigencia::
        where('empresa_id', request()->empresa_id)
        ->orderBy('id', 'desc')
        ->get();

        return view('contigencia.index', compact('data'));
    }

    public function create(){
        return view('contigencia.create');
    }

    public function store(Request $request){
        $this->_validate($request);

        $active = Contigencia::
        where('empresa_id', $request->empresa_id)
        ->where('status', 1)
        ->where('documento', $request->documento)
        ->first();
        if($active){
            session()->flash('flash_erro', "Já existe uma contigência para $request->documento ativada!");
            // return redirect()->back();
        }
        try{
            $item = Contigencia::create([
                'empresa_id' => $request->empresa_id,
                'status' => 1,
                'tipo' => $request->tipo,
                'documento' => $request->documento,
                'motivo' => $request->motivo,
                'status_retorno' => ''
            ]);

            $config = ConfigNota::where('empresa_id', $request->empresa_id)
            ->first();

            $contingency = new Contingency();

            $acronym = $config->cidade->uf;
            $motive = $request->motivo;
            $type = $request->tipo;

            $status_retorno = $contingency->activate($acronym, $motive, $type);
            $item->status_retorno = $status_retorno;
            $item->save();
            session()->flash("flash_sucesso", "Contigencia ativada!");
        }catch(\Exception $e){
            session()->flash('flash_erro', 'Algo deu errado: ' . $e->getMessage());
        }
        return redirect()->route('contigencia.index');
    }

    private function _validate(Request $request){
        $rules = [
            'motivo' => 'required|max:255|min:15'
        ];

        $messages = [
            'motivo.required' => 'O campo nome é obrigatório.',
            'motivo.max' => '255 caracteres maximos permitidos.',
            'motivo.min' => '15 caracteres minímos permitidos.'
        ];
        $this->validate($request, $rules, $messages);
    }

    public function desactive($id){
        $item = Contigencia::findOrFail($id);
        $item->status = 0;

        $contingency = new Contingency($item->status_retorno);
        $status = $contingency->deactivate();

        $item->save();
        session()->flash("mensagem_sucesso", "Contigencia ddesativada!");
        return redirect()->back();

    }

}
