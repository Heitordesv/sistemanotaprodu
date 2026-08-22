<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class ChassiApiController extends Controller
{
    // Página do formulário para consulta por chassi
    public function index()
    {
        return view('veiculo.consulta-chassi');
    }

    // Consulta de veículo pelo chassi via API Brasil
    public function consultar(Request $request)
    {
        $request->validate([
            'chassi' => 'required|string|min:11|max:17'
        ]);

        $chassi = strtoupper($request->chassi);
        $token = env('APIBRASIL_TOKEN');

        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => "https://gateway.apibrasil.io/api/v2/vehicles/base/001/consulta",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => json_encode([
                'tipo' => 'veiculos-dados-v1',
                'chassi' => $chassi,
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

        // DECODIFICAÇÃO DO JSON
        $resultado = json_decode($response, true);

        // PRINT PARA DEBUG
        dd($resultado); // Aqui vai parar a execução e mostrar tudo retornado pela API

        // Variáveis para enviar para a view
        $veiculo = $resultado['data'] ?? [];
        $user = $resultado['user'] ?? [];
        $mensagem = $resultado['message'] ?? '';
        $balance = $resultado['balance'] ?? 0;

        return view('veiculo.resultado-chassi', compact('veiculo', 'chassi', 'user', 'mensagem', 'balance'));
    }

    // Gerar PDF do veículo consultado por chassi
    public function gerarPdf(Request $request)
    {
        $veiculo = json_decode($request->veiculo, true);
        $chassi = $request->chassi;

        $pdf = Pdf::loadView('veiculo.pdf-chassi', compact('veiculo', 'chassi'));
        return $pdf->download("consulta-veiculo-chassi-{$chassi}.pdf");
    }
}
