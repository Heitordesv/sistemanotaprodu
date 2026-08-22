<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Remessa;
use App\Models\Empresa;
use App\Models\Boleto;
use App\Helpers\BoletoHelper;

class RemessaBoletoController extends Controller
{
    public function index(){
        $data = Remessa::orderBy('id', 'desc')
        ->where('empresa_id', request()->empresa_id)
        ->paginate();

        return view('boletos.remessas', compact('data'));
    }

    public function semRemessa(){
        $boletos = Boleto::
        select('boletos.*')
        ->join('conta_recebers', 'conta_recebers.id' , '=', 'boletos.conta_id')
        ->orderBy('boletos.id', 'desc')
        ->where('conta_recebers.empresa_id', request()->empresa_id)
        ->limit(100)
        ->get();

        $data = [];
        foreach($boletos as $b){
            if(!$b->itemRemessa){
                array_push($data, $b);
            }
        }

        return view('boletos.sem_remessa', compact('data'));
    }

    public function store(Request $request){
        $boletos = [];
        for($i=0; $i<sizeof($request->boleto_id); $i++){
            $boleto = Boleto::findOrFail($request->boleto_id[$i]);
            if(!$boleto->itemRemessa){
                array_push($boletos, $boleto);
            }else{
                session()->flash("flash_erro", "Algum dos boletos selecionados esta com remessa gerada!");
                return redirect()->back();
            }
        }

        $bancoId = $boletos[0]->banco_id;
        foreach($boletos as $t){
            if($t->banco_id != $bancoId){
                session()->flash("flash_erro", "Informe os boletos para o mesmo banco para gerar a remessa!");
                return redirect()->back();
            }
        }

        $empresa = Empresa::find($request->empresa_id);
        $boletoHelper = new BoletoHelper($empresa);

        $result = $boletoHelper->gerarRemessaMulti($boletos);

    }

    public function show($id){
        $item = Remessa::findOrFail($id);

        return view('boletos.ver_remessa', compact('item'));
    }

    public function destroy($id){

        $item = Remessa::findOrFail($id);
        try {
            $file = public_path('remessas')."/$item->nome_arquivo.txt";
            $item->delete();
            session()->flash("flash_sucesso", "Remessa removido!");
        } catch (\Exception $e) {
            session()->flash("flash_erro", "Algo deu errado: " . $e->getMessage());
        }
        return redirect()->route('remessa-boletos.index');
    }

    public function download($id){
        try{
            $remessa = Remessa::findOrFail($id);

            if (!__valida_objeto($remessa)) {
                abort(403);
            }

            $file = public_path('remessas')."/$remessa->nome_arquivo.txt";
            if(file_exists($file)){
                header('Content-Type: application/txt');
                header('Content-Disposition: attachment; filename="'.$remessa->nome_arquivo.'.txt"');
                readfile($file);
            }else{
                session()->flash("mensagem_erro", "Arquivo não encontrado!!");
                return redirect('/contasReceber');
            }
            
        }catch(\Exception $e){
            session()->flash("mensagem_erro", "Erro ao baixar: " . $e->getMessage());
        }
    }

}
