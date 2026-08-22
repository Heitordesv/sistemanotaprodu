<?php

namespace App\Http\Controllers\Delivery;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ClienteDelivery;
use App\Models\EnderecoDelivery;
use App\Models\DeliveryConfig;
use Illuminate\Support\Str;
use App\Rules\EmailDupDelivery;
use App\Rules\CelularDupDelivery;
use Comtele\Services\TextMessageService;

class ClienteController extends Controller
{

    public function login(Request $request){

        $cliente = ClienteDelivery::where('celular', preg_replace('/[^0-9]/', '', $request->email_celular))
        ->where('senha', md5($request->senha))
        ->where('empresa_id', $request->empresa_id)
        ->first();

        if($cliente != null){
            return response()->json($cliente, 200);
        }

        $cliente = ClienteDelivery::where('email', $request->email_celular)
        ->where('senha', md5($request->senha))
        ->where('empresa_id', $request->empresa_id)
        ->first();
        if($cliente != null){
            return response()->json($cliente, 200);
        }

        return response()->json("credenciais incorretas!", 404);

    }

    public function enderecoSave(Request $request){
        try{

            $cliente = ClienteDelivery::where('uid', $request->uid)->first();
            $data = [
                'rua' => $request->rua,
                'numero'=> $request->numero,
                'bairro'=> '',
                'bairro_id'=> $request->bairro,
                'referencia'=> $request->complemento ?? '',
                'tipo' => $request->tipo,
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
                'cliente_id' => $cliente->id,
                'cidade_id' => 1
            ];
            $endereco = EnderecoDelivery::create($data);
            return response()->json($endereco, 200);
        }catch(\Exception $e){
            return response()->json("err: " . $e->getMessage(), 404);
        }
    }

    public function enderecoUpdate(Request $request){
        try{

            $endereco = EnderecoDelivery::where('id', $request->id)->first();

            $endereco->fill($request->all())->save();
            return response()->json($endereco, 200);
        }catch(\Exception $e){
            return response()->json("err: " . $e->getMessage(), 404);
        }
    }

    public function clienteSave(Request $request){
        try{

            $cli = ClienteDelivery::where('email', $request->email)
            ->where('empresa_id', $request->empresa_id)->first();

            $config = DeliveryConfig::where('empresa_id', $request->empresa_id)->first();

            if($cli != null){
                return response()->json("email já cadastrado!", 402);
            }

            $celular = preg_replace('/[^0-9]/', '', $request->celular);

            $cli = ClienteDelivery::where('celular', $celular)
            ->where('empresa_id', $request->empresa_id)->first();
            if($cli != null){
                return response()->json("celular já cadastrado!", 402);
            }
            $code = rand() % 9000 + 999;
            $request->merge([
                'senha' => md5($request->senha),
                'celular' => $celular,
                'uid' => Str::random(30),
                'token' => $code
            ]);
            if($config->autenticacao_sms){
                $this->sendSms($celular, $code, $config);
            }
            $cli = ClienteDelivery::create($request->all());
            return response()->json($cli, 200);
        }catch(\Exception $e){
            return response()->json("err: " . $e->getMessage(), 404);
        }
    }

    private function sendSms($phone, $code, $config){

        $nomeEmpresa = $config->nome;
        $content = $nomeEmpresa. " Cóodigo de Autorização ". $code;
        $textMessageService = new TextMessageService(env('SMS_KEY'));
        $res = $textMessageService->send("Sender", $content, [$phone]);
        return $res;
    }

    public function clienteUpdate(Request $request){
        try{
            $item = ClienteDelivery::where('uid', $request->uid)->first();

            $item->nome = $request->nome;
            $item->sobre_nome = $request->sobre_nome;
            $item->email = $request->email;
            $item->celular = $request->celular;
            $item->save();
            return response()->json($item, 200);
        }catch(\Exception $e){
            return response()->json("err: " . $e->getMessage(), 404);
        }
    }

    public function clienteUpdateSenha(Request $request){
        try{
            $item = ClienteDelivery::where('uid', $request->uid)->first();

            $item->senha = md5($request->senha);
            $item->save();
            return response()->json($item, 200);
        }catch(\Exception $e){
            return response()->json("err: " . $e->getMessage(), 404);
        }
    }

    public function findCliente(Request $request){
        try{
            $cliente = ClienteDelivery::where('empresa_id', $request->empresa_id)
            ->with('enderecos')
            ->with('pedidos')
            ->where('uid', $request->uid)->first();

            return response()->json($cliente, 200);
        }catch(\Exception $e){
            return response()->json("err: " . $e->getMessage(), 404);
        }
    }

    public function updateEnderecoPadrao(Request $request){
        $endereco = EnderecoDelivery::findOrFail($request->endereco_id);
        try{

            EnderecoDelivery::where('cliente_id', $endereco->cliente_id)
            ->update(['padrao' => 0]);

            $endereco->padrao = 1;
            $endereco->save();
            return response()->json($endereco, 200);

        }catch(\Exception $e){
            return response()->json("err: " . $e->getMessage(), 404);
        }
    }

    public function sendCode(Request $request){
        $code = $request->code;
        $cliente = ClienteDelivery::where('empresa_id', $request->empresa_id)
        ->where('token', $code)->first();
        if($cliente != null){
            $cliente->ativo = 1;
            $cliente->save();
            return response()->json($cliente, 200);
        }else{
            return response()->json("erro", 404);
        }
    }

    public function refreshCode(Request $request){
        $cliente = ClienteDelivery::where('uid', $request->uid)->first();
        if($cliente != null){

            $config = DeliveryConfig::where('empresa_id', $request->empresa_id)->first();

            $code = rand() % 9000 + 999;
            $cliente->token = $code;
            $this->sendSms($cliente->celular, $code, $config);

            $cliente->save();
            return response()->json($cliente, 200);
        }else{
            return response()->json("erro", 404);
        }
    }
}
