<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class ConsultaVeiculoController extends Controller
{
    // Formulário
    public function index()
    {
        return view('consulta-veiculo.form');
    }

    // Consulta na API Brasil
    public function consultar(Request $request)
    {
        $request->validate([
            'placa' => 'required|string|size:7'
        ]);

        $placa = strtoupper($request->placa);
        $token = env('APIBRASIL_TOKEN');
        $deviceToken = env('APIBRASIL_DEVICE');

        $payload = json_encode([
            'placa' => $placa
        ]);

        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => "https://gateway.apibrasil.io/api/v2/vehicles/dados",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => $payload,
            CURLOPT_HTTPHEADER => [
                "Content-Type: application/json",
                "DeviceToken: {$deviceToken}",
                "Authorization: Bearer {$token}",
            ],
            CURLOPT_TIMEOUT => 120,
        ]);

        $response = curl_exec($curl);
        $err = curl_error($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        if ($err) {
            return back()->withErrors(['erro' => "Erro cURL: $err"]);
        }

        $resultado = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return back()->withErrors(['erro' => "Resposta inválida da API (HTTP $httpCode)."]);
        }

        $dados = $resultado['data'] ?? $resultado;
        $mensagem = $resultado['message'] ?? '';

        return view('consulta-veiculo.resultado', compact('placa', 'dados', 'mensagem'));
    }

    // PDF
    public function gerarPdf(Request $request)
    {
        $dados = json_decode($request->input('dados'), true);
        $placa = $request->input('placa');

        $pdf = Pdf::loadView('consulta-veiculo.pdf', compact('dados', 'placa'));
        return $pdf->download("consulta-veiculo-{$placa}.pdf");
    }
}
