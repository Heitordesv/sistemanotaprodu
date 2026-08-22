<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ConfigNota;
use App\Models\ApiBrasil;
use App\Models\EmpresaDelivery;

class WhatsAppController extends Controller
{
    public function sendMessageToWhatsApp(Request $request)
    {
        // Validação dos dados recebidos
        $dados = $request->validate([
            'telefone' => 'required|string', // Número do telefone do cliente
        ]);

        // Obter a configuração da empresa
        $config = ConfigNota::where('empresa_id', $request->empresa_id)->first();

        if (!$config) {
            return response()->json(['status' => 'error', 'message' => 'Nenhuma configuração encontrada para esta empresa.'], 400);
        }

        if (!$config->user_id) {
            return response()->json(['status' => 'error', 'message' => 'Token de autenticação não configurado para esta empresa.'], 400);
        }

        // Buscar os dados da API
        $data = ApiBrasil::where('user_id', $config->user_id)->first();

        if (!$data || !isset($data->DeviceToken) || !isset($data->Bearer)) {
            return response()->json(['status' => 'error', 'message' => 'Token de autenticação não configurado.'], 400);
        }

        // Buscar o link do cardápio
        $nome_empresa_link = EmpresaDelivery::where('user_id', $config->user_id)
                                            ->value('nome_empresa_link');

        // Gerar a mensagem a ser enviada
        $mensagemEnvia = "Olá! Aqui está o nosso cardápio completo: https://deliveryba.com.br/$nome_empresa_link 🍧📱";

        // Preparar os dados para o envio
        $jsonData = json_encode([
            'number' => $dados['telefone'],
            'text' => $mensagemEnvia,
        ]);

        // Enviar a mensagem via cURL
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => "https://gateway.apibrasil.io/api/v2/whatsapp/sendText",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => $jsonData,
            CURLOPT_HTTPHEADER => [
                "Content-Type: application/json",
                "DeviceToken: {$data->DeviceToken}",
                "Authorization: Bearer {$data->Bearer}",
            ],
        ]);

        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);

        if ($err) {
            return response()->json(['status' => 'error', 'message' => 'Erro ao enviar a mensagem: ' . $err], 500);
        }

        $responseData = json_decode($response, true);

       

if (!isset($responseData['status']) || $responseData['status'] !== 'success') {
    $motivoErro = $responseData['message'] ?? 'Erro desconhecido.';
    return redirect()->back()->with('error', "Falha ao enviar a mensagem: $motivoErro");
}

return redirect()->back()->with('success', 'Mensagem enviada com sucesso via WhatsApp!');    }
}
