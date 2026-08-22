<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ContaReceber;
use App\Models\ContaBancaria;
use App\Models\Empresa;
use App\Models\Boleto;
use App\Helpers\BoletoHelper;

class BoletoController extends Controller
{
   
    // === LISTAGEM DE BOLETOS ===
    public function index()
    {
        $data = Boleto::orderBy('created_at', 'desc')->paginate(15);
        return view('boletos.remessas', compact('data'));
    }
    
    public function store(Request $request){
        $contas = [];
        for($i=0; $i<sizeof($request->conta_id); $i++){
            $conta = ContaReceber::findOrFail($request->conta_id[$i]);
            array_push($contas, $conta);
        }

        $contasBancarias = ContaBancaria::where('empresa_id', $request->empresa_id)
        ->get();

        return view('boletos.create', compact('contas', 'contasBancarias'));
    }

    public function storeIssue(Request $request){
        $empresa = Empresa::find($request->empresa_id);
        $boletoHelper = new BoletoHelper($empresa);

        $boletos = [];

        for($i=0; $i<sizeof($request->numero_boleto); $i++){
            $data = [
                'banco_id' => $request->conta_bancaria_id,
                'conta_id' => $request->conta_id[$i],
                'numero' => $request->numero_boleto[$i],
                'numero_documento' => $request->numero_documento[$i],
                'carteira' => $request->carteira,
                'convenio' => $request->convenio,
                'linha_digitavel' => '',
                'nome_arquivo' => '',
                'juros' => $request->juros[$i] ? __convert_value_bd($request->juros[$i]) : 0,
                'multa' => $request->multa[$i] ? __convert_value_bd($request->multa[$i]) : 0,
                'juros_apos' => $request->juros_apos[$i],
                'instrucoes' => "",
                'logo' => $request->usar_logo,
                'tipo' => $request->tipo,
                'codigo_cliente' => isset($request->codigo_cliente) ? $request->codigo_cliente : '',
                'posto' => isset($request->posto) ? $request->posto : ''
            ];

            array_push($boletos, $data);

        }

        $result = $boletoHelper->simular($boletos);

        if(isset($result['erro'])){
            session()->flash("flash_erro", $result['mensagem']);

        }else{
            $result = $this->gerarMultiStore($boletos, $empresa);
            session()->flash("flash_sucesso", "Boletos cadastrados!");
        }
        return redirect()->route('conta-receber.index');
    }

    private function gerarMultiStore($boletos, $empresa){

        $boletoHelper = new BoletoHelper($empresa);
        foreach($boletos as $b){
            $boleto = Boleto::create($b);
            $result = $boletoHelper->gerar($boleto);
        }
        return $result;
    }

    public function print($id){

        $boleto = Boleto::find($id);
        if (!__valida_objeto($boleto->conta)) {
            abort(403);
        }
        $link = "/boletos_arquivos/$boleto->nome_arquivo.pdf";

        $file = public_path('boletos_arquivos')."/$boleto->nome_arquivo.pdf";
        if(file_exists($file)){
            return redirect($link);
        }else{
            session()->flash("flash_erro", "Arquivo não encontrado!!");
            return redirect()->route('conta-receber.index');
        }
        
    }
}
