<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\NuvemShopConfig;

class NuvemShopAuthController extends Controller
{

    public function index(Request $request){
        $config = $this->getConfig();
        if($config != null){

            $auth = new \TiendaNube\Auth($config->client_id, $config->client_secret);
            $url = $auth->login_url_brazil();
            return redirect($url);
        }else{
            session()->flash("flash_erro", "Configure as credênciais!");
            return redirect()->route('nuvemshop.index');
        }
    }

    private function getConfig(){
        return NuvemShopConfig::where('empresa_id', request()->empresa_id)->first();
    }

    public function auth(Request $request){
        $config = $this->getConfig();
        if($config != null){
            $code = $request->code;
            $auth = new \TiendaNube\Auth($config->client_id, $config->client_secret);
            $store_info = $auth->request_access_token($code);

            $store_info['email'] = $config->email;

            session(['store_info' => $store_info]);

            session()->flash("flash_sucesso", "Autenticação realizada, access_token: " . $store_info['access_token'] . " store id: " . $store_info['store_id']);

            return redirect()->route('nuvemshop-pedidos.index');
        }else{
            session()->flash("flash_erro", "Configure as credênciais!");
            return redirect()->route('nuvemshop.index');

        }
    }
}
