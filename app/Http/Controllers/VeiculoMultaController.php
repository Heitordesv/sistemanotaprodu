<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class VeiculoMultaController extends Controller
{
    public function index()
    {
        return view('veiculo.consulta-multa');
    }

    public function consultar(Request $request)
    {
        $request->validate([
            'placa' => 'required|string|size:7',
        ]);

        $placa = strtoupper($request->placa);
        $token = env('APIBRASIL_TOKEN');
      // dd($token);
     // dd($placa);
        $payload = [
            'tipo' => 'multas',
            'placa' => $placa,
            'homolog' => false
        ];

        $curl = curl_init("https://gateway.apibrasil.io/api/v2/vehicles/base/001/consulta");
        curl_setopt_array($curl, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_HTTPHEADER => [
                "Authorization: Bearer $token",
                "Content-Type: application/json"
            ],
            CURLOPT_TIMEOUT => 120,
        ]);

        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);

        if ($err) {
            return back()->withErrors(['erro' => "Erro na consulta: $err"]);
        }

        $resultado = json_decode($response, true);
       //dd($resultado);

        $user = $resultado['user'] ?? [];
        $data = $resultado['data'] ?? [];
        $mensagem = $resultado['message'] ?? '';
        $balance = $resultado['balance'] ?? 0;
        $homolog = $resultado['homolog'] ?? false;

        return view('veiculo.resultado-multa', compact('user', 'data', 'mensagem', 'balance', 'homolog'));
    }

    public function gerarPdf(Request $request)
    {
        $user = json_decode($request->user, true) ?? [];
        $data = json_decode($request->data, true) ?? [];
        $mensagem = $request->mensagem ?? '';
        $balance = $request->balance ?? 0;
        $homolog = filter_var($request->homolog, FILTER_VALIDATE_BOOLEAN);

        $placa = $data['placa'] ?? 'desconhecida';

        $pdf = Pdf::loadView('veiculo.pdf-multa', compact('user', 'data', 'mensagem', 'balance', 'homolog'));
        return $pdf->download("consulta-multa-{$placa}.pdf");
    }
}
