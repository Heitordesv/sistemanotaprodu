<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\ConfigEcommerce;
// header("Access-Control-Allow-Origin: *");
// header("Access-Control-Allow-Headers: Authorization, Origin, X-Requested-With, Content-Type,      Accept");
// header("Content-Type: application/json");
class AuthEcommerce
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
        $token = $request->header('Authorization');

        $config = ConfigEcommerce::
        where('api_token', $token)
        ->first();

        if($config == null){
            return response()->json("", 404);
        }

        if(!$config->usar_api){
            return response()->json("api desmarcada", 401);
        }

        $request->merge(['empresa_id' => $config->empresa_id]);
        return $next($request);
    }
}
