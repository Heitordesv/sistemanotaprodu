<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class DebitosController extends Controller
{
    // Página do formulário
    public function index()
    {
        return view('debitos.consulta');
    }

    // Consulta API e gera PDF ou exibe erro
    public function consultar(Request $request)
    {
        // Limpa CPF: remove pontos, traços e espaços
        $cpfRaw = preg_replace('/\D/', '', $request->cpf);
        $cpfFormatado = substr($cpfRaw,0,3).'.'.substr($cpfRaw,3,3).'.'.substr($cpfRaw,6,3).'-'.substr($cpfRaw,9,2);

        // Payload para API
        $payload = [
            "cpf" => $cpfRaw,       // CPF limpo, sem pontos/traços
            "tipo" => "spc-serasa",
            "homolog" => false       // true para testes, false em produção
        ];
//dd($payload );
        // cURL
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => "https://gateway.apibrasil.io/api/v2/debitos/cpf/credits",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_HTTPHEADER => [
                "Content-Type: application/json",
                "Authorization: Bearer " . env('APIBRASIL_TOKEN')
            ],
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_TIMEOUT => 120
        ]);

        $response = curl_exec($ch);
        dd($response);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            return back()->withErrors(['Erro ao consultar API: ' . $error]);
        }

        $data = json_decode($response, true);

        // Se a API retornar erro
        if(isset($data['error']) && $data['error'] === true) {
            return back()->with('api_error', $data['message'] ?? 'Erro desconhecido');
        }

        // Caso não tenha erro, gerar PDF
        $pdf = Pdf::loadView('debitos.pdf', [
            'data' => $data,
            'cpf'  => $cpfFormatado
        ]);

        return $pdf->download('consulta-debitos-' . $cpfRaw . '.pdf');
    }
}
