<?php

namespace App\Http\Controllers;

use App\Models\IBPT;
use App\Models\ItemIBTE;
use Illuminate\Http\Request;

class IbptController extends Controller
{
    public function index(){

        $data = IBPT::all();

        return view('ibpt.index', compact('data'));
    }

    public function create(){
        $todos = IBPT::estados();
        $estados = [];
        foreach($todos as $uf){
            $res = IBPT::where('uf', $uf)->first();
            if($res == null){
                $estados[$uf] = $uf;
            }
        }
        return view('ibpt.create', compact('estados'));
    }

    public function edit($id){
        $item = IBPT::findOrFail($id);

        return view('ibpt.edit', compact('item'));
    }

    public function store(Request $request){

        if ($request->hasFile('file')){
            $file = $request->file;
            $handle = fopen($file, "r");
            $row = 0;
            $linhas = [];

            $result = IBPT::create(
                [
                    'uf' => $request->uf,
                    'versao' => $request->versao,
                ]
            );


            while ($line = fgetcsv($handle, 1000, ";")) {
                if ($row++ == 0) {
                    continue;
                }
                
                $data = [
                    'ibte_id' => $result->id,
                    'codigo' => $line[0],
                    'descricao' => $line[3],
                    'nacional_federal' => $line[4],
                    'importado_federal' => $line[5],
                    'estadual' => $line[6],
                    'municipal' => $line[7] 
                ];
                ItemIBTE::create($data);

            }
            
            session()->flash('flash_sucesso', 'Importação concluída para '.$request->uf);

        }

        return redirect()->route('ibpt.index');

    }

    public function update(Request $request, $id){

        $item = IBPT::findOrFail($id);

        if ($request->hasFile('file')){
            $file = $request->file;
            $handle = fopen($file, "r");
            $row = 0;
            $linhas = [];


            $item->versao = $request->versao;
            $item->save();
            ItemIBTE::where('ibte_id', $item->id)->delete();

            while ($line = fgetcsv($handle, 1000, ";")) {
                if ($row++ == 0) {
                    continue;
                }
                
                $data = [
                    'ibte_id' => $id,
                    'codigo' => $line[0],
                    'descricao' => $line[3],
                    'nacional_federal' => $line[4],
                    'importado_federal' => $line[5],
                    'estadual' => $line[6],
                    'municipal' => $line[7] 
                ];
                ItemIBTE::create($data);

            }
            session()->flash('flash_sucesso', 'Importação atualizada para '.$request->uf);

        }else{
            $item->versao = $request->versao;
            $item->save();
            session()->flash('flash_sucesso', 'Importação atualizada para '.$request->uf);
        }

        return redirect()->route('ibpt.index');

    }

    public function show($id){
        $ibpt = IBPT::find($id);
        $itens = ItemIBTE::where('ibte_id', $id)->paginate(100);
        return view('ibpt.show', compact('ibpt', 'itens'));
    }
}
