<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class ConsuVeiculoController extends Controller
{
    // =========================
    // Consulta de Roubo/Furto
    // =========================
    public function index()
    {
        return view('consultarveiculo.consulta-veiculo');
    }

    public function consultar(Request $request)
    {
        $placa = strtoupper($request->placa);
        $token = env('APIBRASIL_TOKEN');

        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => "https://gateway.apibrasil.io/api/v2/vehicles/base/001/consulta",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => json_encode([
                'tipo' => 'roubo-furto',
                'placa' => $placa,
                'homolog' => false
            ]),
            CURLOPT_HTTPHEADER => [
                "Authorization: Bearer $token",
                "Content-Type: application/json"
            ],
        ]);

        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);

        if ($err) return back()->withErrors(['msg' => "Erro na consulta: $err"]);

        $veiculo = json_decode($response, true)['data'] ?? [];

        return view('consultarveiculo.consulta-veiculo', compact('veiculo', 'placa'));
    }

    public function gerarPdf(Request $request)
    {
        $veiculo = json_decode($request->veiculo, true);
        $placa = $request->placa;

        $pdf = Pdf::loadView('consultarveiculo.relatorio-pdf', compact('veiculo', 'placa'));
        return $pdf->download("consulta-veiculo-{$placa}.pdf");
    }

    // =========================
    // Consulta de Proprietário
    // =========================
    public function indexProprietario()
    {
        return view('consultarveiculo.consulta-proprietario');
    }

    public function consultarProprietario(Request $request)
    {
        $placa = strtoupper($request->placa);
        $token = env('APIBRASIL_TOKEN');

        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => "https://gateway.apibrasil.io/api/v2/vehicles/base/001/consulta",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => json_encode([
                'tipo' => 'proprietario-detalhes',
                'placa' => $placa,
                'homolog' => false
            ]),
            CURLOPT_HTTPHEADER => [
                "Authorization: Bearer $token",
                "Content-Type: application/json"
            ],
        ]);

        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);

        if ($err) return back()->withErrors(['msg' => "Erro na consulta: $err"]);

        $resultado = json_decode($response, true);
        $veiculo = $resultado['data'] ?? [];
        $proprietario = $resultado['user'] ?? [];

        return view('consultarveiculo.consulta-proprietario', compact('veiculo', 'proprietario', 'placa'));
    }

    public function gerarPdfProprietario(Request $request)
    {
        $veiculo = json_decode($request->veiculo, true);
        $proprietario = json_decode($request->proprietario, true);
        $placa = $request->placa;

        $pdf = Pdf::loadView('consultarveiculo.relatorio-pdf-proprietario', compact('veiculo', 'proprietario', 'placa'));
        return $pdf->download("consulta-proprietario-{$placa}.pdf");
    }

    // =========================
    // Consulta de Multas e Infrações
    // =========================
    public function indexMultas()
    {
        return view('consultarveiculo.consulta-multas');
    }

    public function consultarMultas(Request $request)
    {
        $placa = strtoupper($request->placa);
        $token = env('APIBRASIL_TOKEN');

        $postData = json_encode([
            'tipo' => 'renainf',
            'placa' => $placa,
            'homolog' => false
        ]);

        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => "https://gateway.apibrasil.io/api/v2/vehicles/base/001/consulta",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_HTTPHEADER => [
                "Content-Type: application/json",
                "Authorization: Bearer $token"
            ],
            CURLOPT_POSTFIELDS => $postData,
        ]);

        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);

        if ($err) return back()->withErrors(['msg' => "Erro cURL: $err"]);

        $resultado = json_decode($response, true);
        $veiculo = $resultado['data'] ?? [];
        $multas = $veiculo['registros'] ?? [];
        $user = $resultado['user'] ?? [];

        return view('consultarveiculo.consulta-multas', compact('veiculo', 'multas', 'placa', 'user'));
    }

    public function gerarPdfMultas(Request $request)
    {
        $veiculo = json_decode($request->veiculo, true);
        $multas = json_decode($request->multas, true);
        $placa = $request->placa;

        $pdf = Pdf::loadView('consultarveiculo.relatorio-pdf-multas', compact('veiculo', 'multas', 'placa'));
        return $pdf->download("consulta-multas-{$placa}.pdf");
    }

    // =========================
    // Consulta Geral de Veículo
    // =========================
// Controller
public function indexVeiculog()
{
    return view('consultarveiculo.consulta-geral');
}
public function consultarVeiculog(Request $request)
{
    $request->validate([
        'placa' => 'required|string|size:7',
    ]);

    $placa = strtoupper($request->placa);
    $token = env('APIBRASIL_TOKEN');

    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_URL => "https://gateway.apibrasil.io/api/v2/vehicles/base/001/consulta",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_POSTFIELDS => json_encode([
            'tipo' => 'dados',
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

    if ($err) return back()->withErrors(['msg' => "Erro na consulta: $err"]);

    $resultado = json_decode($response, true);
    $veiculo = $resultado['data'] ?? [];
    $user = $resultado['user'] ?? [];

    return view('consultarveiculo.consulta-geral', compact('veiculo', 'placa', 'user'));
}

    public function gerarPdfVeiculo(Request $request)
    {
        $veiculo = json_decode($request->veiculo, true);
        $placa = $request->placa;

        $pdf = Pdf::loadView('consultarveiculo.relatorio-pdf-veiculo', compact('veiculo', 'placa'));
        return $pdf->download("consulta-veiculo-{$placa}.pdf");
    }
    
    // Exibir formulário
public function indexRecall()
{
    return view('consultarveiculo.consulta-recall');
}

// Consultar recall via API
public function consultarRecall(Request $request)
{
    $placa = strtoupper($request->placa);
    $token = env('APIBRASIL_TOKEN');

    $postData = json_encode([
        'tipo' => 'recall',
        'placa' => $placa,
        'homolog' => true
    ]);

    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_URL => "https://gateway.apibrasil.io/api/v2/vehicles/base/001/consulta",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_HTTPHEADER => [
            "Content-Type: application/json",
            "Authorization: Bearer $token"
        ],
        CURLOPT_POSTFIELDS => $postData,
    ]);

    $response = curl_exec($curl);
    $err = curl_error($curl);
    curl_close($curl);

    if ($err) return back()->withErrors(['msg' => "Erro na consulta: $err"]);

    $resultado = json_decode($response, true);
    $veiculo = $resultado['data'] ?? [];

    return view('consultarveiculo.consulta-recall', compact('veiculo', 'placa'));
}

// Gerar PDF
public function gerarPdfRecall(Request $request)
{
    $veiculo = json_decode($request->veiculo, true);
    $placa = $request->placa;

    $pdf = Pdf::loadView('consultarveiculo.relatorio-pdf-recall', compact('veiculo', 'placa'));
    return $pdf->download("recall-veiculo-{$placa}.pdf");
}
}
