<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class ConsultarcpfController extends Controller
{
    // Página do formulário
    public function index()
    {
        return view('cpf.consulta');
    }

    // Consulta de dados via API Brasil (CPF)
    public function consultar(Request $request)
    {
        $request->validate([
            'cpf' => 'required|string'
        ]);

        // remove tudo que não for dígito
        $cpfRaw = preg_replace('/\D/', '', $request->input('cpf'));

        if (strlen($cpfRaw) !== 11) {
            return back()->withErrors(['cpf' => 'CPF inválido. Informe 11 dígitos.'])->withInput();
        }

        $cpf = $cpfRaw;
        $token = env('APIBRASIL_TOKEN');

        if (empty($token)) {
            return back()->withErrors(['erro' => 'Token da API não configurado. Verifique .env (APIBRASIL_TOKEN).']);
        }

        $payload = json_encode([
            'cpf' => $cpf
        ]);

        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => "https://gateway.apibrasil.io/api/v2/dados/cpf",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => $payload,
            CURLOPT_HTTPHEADER => [
                "Authorization: Bearer {$token}",
                "Content-Type: application/json",
                "Content-Length: " . strlen($payload)
            ],
            CURLOPT_TIMEOUT => 120,
            CURLOPT_CONNECTTIMEOUT => 15,
        ]);

        $response = curl_exec($curl);
        $err = curl_error($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        if ($err) {
            return back()->withErrors(['erro' => "Erro na consulta: $err"]);
        }

        // Tenta decodificar JSON — se falhar, mostra erro
        $resultado = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return back()->withErrors(['erro' => "Resposta inválida da API (não é JSON). HTTP_CODE: {$httpCode}"]);
        }

        // Ajuste conforme retorno da API: aqui assumo estrutura parecida com a sua consulta anterior
        $dados = $resultado['data'] ?? $resultado; // caso a API retorne em root
        $user = $resultado['user'] ?? [];
        $mensagem = $resultado['message'] ?? '';
        $balance = $resultado['balance'] ?? 0;

        return view('cpf.resultado', compact('dados', 'cpf', 'user', 'mensagem', 'balance'));
    }

    // Gerar PDF dos dados consultados
    public function gerarPdf(Request $request)
    {
        // Recebe os dados já serializados em JSON (por exemplo via form hidden)
        $dados = json_decode($request->input('dados'), true);
        $cpf = $request->input('cpf');

        if ($dados === null) {
            return back()->withErrors(['erro' => 'Dados para geração de PDF inválidos.']);
        }

        $pdf = Pdf::loadView('cpf.pdf', compact('dados', 'cpf'));
        return $pdf->download("consulta-cpf-{$cpf}.pdf");
    }
}
