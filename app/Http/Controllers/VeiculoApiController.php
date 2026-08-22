<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class VeiculoApiController extends Controller
{
    // Página do formulário
    public function index()
    {
        return view('veiculo.consulta');
    }

    // Consulta de dados do veículo via API Brasil
    public function consultar(Request $request)
    {
        $request->validate([
            'placa' => 'required|string|size:7'
        ]);

        $placa = strtoupper($request->placa);
        $token = env('APIBRASIL_TOKEN');

        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => "https://gateway.apibrasil.io/api/v2/vehicles/base/001/consulta",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => json_encode([
                'tipo' => 'veiculos-dados-v1',
                'placa' => $placa,
                'homolog' => false
            ]),
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

        $veiculo = $resultado['data'] ?? [];
        $user = $resultado['user'] ?? [];
        $mensagem = $resultado['message'] ?? '';
        $balance = $resultado['balance'] ?? 0;

        return view('veiculo.resultado', compact('veiculo', 'placa', 'user', 'mensagem', 'balance'));
    }

    // Gerar PDF do veículo
    public function gerarPdf(Request $request)
    {
        $veiculo = json_decode($request->veiculo, true);
        $placa = $request->placa;

        $pdf = Pdf::loadView('veiculo.pdf', compact('veiculo', 'placa'));
        return $pdf->download("consulta-veiculo-{$placa}.pdf");
    }
}
