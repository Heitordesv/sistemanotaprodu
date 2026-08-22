<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Cidade;
use App\Models\Payment;
use App\Models\ContaBancaria;

class HelperController extends Controller
{
    public function cidadePorNome($nome){
        $cidade = Cidade::
        where('nome', $nome)
        ->first();

        return response()->json($cidade, 200);
    }

    public function cidadePorCodigoIbge($codigo){
        $cidade = Cidade::
        where('codigo', $codigo)
        ->first();

        return response()->json($cidade, 200);
    }

    public function contaBancaria($id){
        $item = ContaBancaria::findOrfail($id);

        return response()->json($item, 200);
    }

    public function buscaCidades(Request $request){
        $data = Cidade::
        where('nome', 'like', "%$request->pesquisa%")
        ->get();

        return response()->json($data, 200);
        
    }

    public function consultaPagamentoPix($transacao_id){
        if(env("MERCADOPAGO_AMBIENTE") == 'sandbox'){
            \MercadoPago\SDK::setAccessToken(env("MERCADOPAGO_ACCESS_TOKEN"));
        }else{
            \MercadoPago\SDK::setAccessToken(env("MERCADOPAGO_ACCESS_TOKEN_PRODUCAO"));
        }
        $payment = Payment::where('transacao_id', $transacao_id)
        ->first();

        if($payment){
            $payStatus = \MercadoPago\Payment::find_by_id($payment->transacao_id);
            // $payStatus->status = "approved";
            $payment->status = $payStatus->status;
            $payment->save();

            if($payStatus->status == "approved"){
                $this->setarLicenca($payment->plano);
            }
            return response()->json($payStatus->status, 200);

        }else{
            return response()->json("erro", 404);
        }
    }

    private function setarLicenca($planoEmpresa){
        $plano = $planoEmpresa->plano;
        $exp = date('Y-m-d', strtotime("+$plano->intervalo_dias days",strtotime( 
          date('Y-m-d'))));

        $planoEmpresa->expiracao = $this->parseDate($exp);
        $planoEmpresa->save();
    }

    private function parseDate($date){
        return date('Y-m-d', strtotime(str_replace("/", "-", $date)));
    }
}
