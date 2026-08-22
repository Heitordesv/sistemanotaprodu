<?php

namespace App\Http\Middleware;

use Closure;
use Response;
use App\Models\VendaCaixa;
use App\Models\ConfigNota;

class ValidNFCe
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
    	$vendaId = $request->vendaId;
    	$venda = VendaCaixa::where('id', $vendaId)->first();

    	$errArr = [];
    	$errArr = $this->validaEmitente($errArr);
    	$errArr = $this->validaENV($errArr);

    	if(empty($errArr)) return $next($request);
    	else return Response::json($errArr, 404);	
    }

    private function validaEmitente($errArr){
    	$config = ConfigNota::first();
    	if($config == null){
    		array_push($errArr, "Configure um emitente");
    		return $errArr;
    	}
    	if(strlen($config->razao_social) < 3){
    		array_push($errArr, "Razao social emitente inválida");	
    	}
    	if(strlen($config->nome_fantasia) < 3){
    		array_push($errArr, "Nome fantasia emitente inválido");	
    	}
    	if(strlen($config->cnpj) < 11){
    		array_push($errArr, "CNPJ emitente inválido");	
    	}
    	if(strlen($config->codMun) < 6){
    		array_push($errArr, "Código do municipio emitente inválido");	
    	}
    	if(strlen($config->cep) < 9){
    		array_push($errArr, "CEP emitente inválido");	
    	}
        if(strlen($config->csc) < 5){
            array_push($errArr, "Configure o CSC, caso não possua informe AAAAA");  
        }
        if(strlen($config->csc_id) < 6){
            array_push($errArr, "Configure o CSCid, caso não possua informe 000001");   
        }
    	return $errArr;
    }

    private function validaENV($errArr){
    	
    	if(strlen(getenv('RESP_CNPJ')) < 14){
    		array_push($errArr, "Configure o CNPJ do responsável tecnico no arquivo .env");	
    	}
    	if(strlen(getenv('RESP_NOME')) < 3){
    		array_push($errArr, "Configure o nome do responsável tecnico no arquivo .env");	
    	}
    	if(strlen(getenv('RESP_EMAIL')) < 10){
    		array_push($errArr, "Configure o email do responsável tecnico no arquivo .env");	
    	}
    	if(strlen(getenv('RESP_FONE')) < 10){
    		array_push($errArr, "Configure o telefone do responsável tecnico no arquivo .env");	
    	}
    	return $errArr;
    }


}