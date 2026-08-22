<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class VeiculoCrlvController extends Controller
{
    // Página do formulário para consulta CRLV
    public function index()
    {
        return view('veiculo.consulta-crlv');
    }

    // Consulta de veículo via CRLV
    public function consultar(Request $request)
    {
        $request->validate([
            'placa' => 'required|string|size:7',
            'uf' => 'required|string|size:2'
        ]);

        $placa = strtoupper($request->placa);
        $uf = strtoupper($request->uf);
        $token = env('APIBRASIL_TOKEN');

        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => "https://gateway.apibrasil.io/api/v2/vehicles/base/001/consulta",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => json_encode([
                'tipo' => 'crlv',
                'placa' => $placa,
                'uf' => $uf,
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

        // DEBUG
        // dd($resultado);

        $veiculo = $resultado['data'] ?? [];
        $mensagem = $resultado['message'] ?? '';
        $balance = $resultado['balance'] ?? 0;

        return view('veiculo.resultado-crlv', compact('veiculo', 'placa', 'uf', 'mensagem', 'balance'));
    }

    // Gerar PDF do CRLV
    public function gerarPdf(Request $request)
    {
        $veiculo = json_decode($request->veiculo, true);
        $placa = $request->placa;
        $uf = $request->uf;

        $pdf = Pdf::loadView('veiculo.pdf-crlv', compact('veiculo', 'placa', 'uf'));
        return $pdf->download("consulta-crlv-{$placa}-{$uf}.pdf");
    }
}
