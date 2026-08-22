<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\ClienteDelivery;

class AuthKey
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
        $token = $request->header('auth');
        // return response()->json($request->header(), 401);
        $spl = explode(";", base64_decode($token));

        $cli = ClienteDelivery::where('id', $spl[1])->first();

        $tkGenerate = '';

        if($cli){
            $tkGenerate = "$cli->nome;$cli->id;$cli->email";
            $tkGenerate = base64_encode($tkGenerate);
        }

        if($tkGenerate != $token){
            return response()->json($token, 401);
        }else{
           $request->merge(['cliente' => $spl[1]]);
        }

        return $next($request);
    }
}
