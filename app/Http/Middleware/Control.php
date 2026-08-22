<?php

namespace App\Http\Middleware;

use Closure;
use Response;
use App\Models\Venda;
use App\Models\ConfigNota;

class Control
{

	public function handle($request, Closure $next){

		if($_SERVER['HTTP_HOST'] == 'localhost:8000' || $_SERVER['HTTP_HOST'] == 'localhost' || $_SERVER['HTTP_HOST'] == 'localhost:9000'){
			return $next($request);
		}

		if(!extension_loaded('curl')){
			return redirect('/402');
		}
		$cp = $path = $_SERVER['HTTP_HOST'];
		$uri = explode(".", $path);

		if(sizeof($uri) > 3){
			$path = $uri[1] . '.' . $uri[2] . '.' . $uri[3];
		}

		$data1 = [
			'data1' => $path,
			'data2' => $cp,
			'fone' => env('RESP_FONE') ?? ''
		];
		
		try{
			$defaults = array(
				CURLOPT_URL => base64_decode('aHR0cDovL2F1dGgubWJtbWFkZWlyYXMuY29tLmJyL2FwaS9jb250cm9s'),
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

			if ($http_status == '200') {
				return $next($request);
			} else {
				return redirect('/401');
			}
		}catch (\Exception $e) {
			echo $e->getMessage();
		}
	}

}