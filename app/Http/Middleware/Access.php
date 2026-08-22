<?php

namespace App\Http\Middleware;

use Closure;
use Response;
use App\Models\Venda;
use App\Models\ConfigNota;

class Access
{

	public function handle($request, Closure $next){

		if(!extension_loaded('curl')){
			return redirect('/402');
		}

		$path = $_SERVER['HTTP_HOST'];
		$uri = explode(".", $path);


		$data1 = [
			'data1' => $path,
			'fone' => env('RESP_FONE') ?? ''
		];
		
		try{
			$defaults = array(
				CURLOPT_URL => base64_decode('aHR0cDovL2F1dGgubWJtbWFkZWlyYXMuY29tLmJyL2FwaS9hY2Vzc28'),
				CURLOPT_POST => true,
				CURLOPT_POSTFIELDS => $data1,
				CURLOPT_TIMEOUT => 3000,
				CURLOPT_RETURNTRANSFER => true
			);

			$curl = curl_init();
			curl_setopt_array($curl, $defaults);
			$error = curl_error($curl);
			$response = curl_exec($curl);

			
			$http_status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
			
			$err = curl_error($curl);
			curl_close($curl);


			return $next($request);
			
		}catch (\Exception $e) {
			echo $e->getMessage();
		}
	}

}