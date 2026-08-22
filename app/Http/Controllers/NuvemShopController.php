<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\NuvemShopConfig;

class NuvemShopController extends Controller
{
    public function index(Request $request)
    {
        $config = NuvemShopConfig::
        where('empresa_id', $request->empresa_id)
        ->first();

        return view('nuvemshop/config', compact('config'));
    }

    public function store(Request $request){
        $config = NuvemShopConfig::
        where('empresa_id', $request->empresa_id)
        ->first();
        try{
            if($config == null){
                NuvemShopConfig::create([
                    'client_id' => $request->client_id,
                    'client_secret' => $request->client_secret,
                    'email' => $request->email,
                    'empresa_id' => $request->empresa_id
                ]);
                session()->flash("flash_sucesso", "Configuração cadastrada!");

            }else{
                $config->fill($request->all())->save();
                $config->save();
                session()->flash("flash_sucesso", "Configuração atualizada!");
            }
        }catch(\Exception $e){
            session()->flash('flash_erro', 'Algo deu errado: ' . $e->getMessage());
        }
        return redirect()->back();
    }

    private function _validateConfig(Request $request){
        $rules = [
            'client_id' => 'required',
            'client_secret' => 'required',
            'email' => 'required',
        ];

        $messages = [
            'client_id.required' => 'Campo obrigatório.',
            'client_secret.required' => 'Campo obrigatório.',
            'email.required' => 'Campo obrigatório.'
        ];
        $this->validate($request, $rules, $messages);
    }
}
