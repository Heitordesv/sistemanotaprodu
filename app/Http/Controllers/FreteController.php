<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Client;
use App\Models\FreteEscolhido;

class FreteController extends Controller
{
  public function index(Request $request)
{
    $empresaId = session('user_logged')['empresa'];

    $query = FreteEscolhido::where('empresa_id', $empresaId);

    if ($request->cep_origem) {
        $query->where('cep_origem', 'like', "%{$request->cep_origem}%");
    }

    if ($request->cep_destino) {
        $query->where('cep_destino', 'like', "%{$request->cep_destino}%");
    }

    $fretes = $query->orderBy('created_at', 'desc')->get();

    return view('frete.form', compact('fretes'));
}
    // Calcula frete via API
    public function calcular(Request $request)
    {
        $request->validate([
            'cep_origem'  => 'required|string',
            'cep_destino' => 'required|string',
            'peso'        => 'required|numeric',
            'largura'     => 'required|numeric',
            'altura'      => 'required|numeric',
            'comprimento' => 'required|numeric',
            'valor'       => 'required|numeric',
        ]);

        $client = new Client();

        try {
            $response = $client->post('https://www.melhorenvio.com.br/api/v2/me/shipment/calculate', [
                'headers' => [
                    'Accept'        => 'application/json',
                    'Authorization' => 'Bearer ' . env('MELHORENVIO_TOKEN'),
                    'Content-Type'  => 'application/json',
                    'User-Agent'    => 'MinhaAplicacao (meuemail@dominio.com)',
                ],
                'json' => [
                    "from" => ["postal_code" => $request->cep_origem],
                    "to"   => ["postal_code" => $request->cep_destino],
                    "products" => [[
                        "id"              => "1",
                        "weight"          => $request->peso,
                        "width"           => $request->largura,
                        "height"          => $request->altura,
                        "length"          => $request->comprimento,
                        "insurance_value" => $request->valor,
                        "quantity"        => 1
                    ]],
                    "options" => [
                        "receipt"   => false,
                        "own_hand"  => false,
                        "collect"   => false
                    ],
                    "services" => "1,2"
                ]
            ]);

            $data = json_decode($response->getBody(), true);

            return view('frete.resultado', compact('data', 'request'));

        } catch (\Exception $e) {
            return back()->withErrors('Erro ao calcular frete: ' . $e->getMessage());
        }
    }

  public function escolher(Request $request)
{
    $request->validate([
        'name'          => 'required|string',
        'price'         => 'required|numeric',
        'delivery_time' => 'required|integer',
        'cep_origem'    => 'required|string',
        'cep_destino'   => 'required|string',
        'empresa_id'    => 'required|integer', // adicionei validação para empresa_id
    ]);

    FreteEscolhido::create([
        'empresa_id'    => $request->empresa_id,
        'name'          => $request->name,
        'price'         => $request->price,
        'delivery_time' => $request->delivery_time,
        'cep_origem'    => $request->cep_origem,
        'cep_destino'   => $request->cep_destino,
    ]);

    return redirect()->route('frete.form')->with('success', 'Frete escolhido com sucesso!');
}
 
}
