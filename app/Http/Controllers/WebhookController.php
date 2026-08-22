<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    public function handle(Request $request)
    {
        $event = $request->input('event');
        $data = $request->input('data');

        Log::info('🔔 Webhook recebido', [
            'event' => $event,
            'data' => $data,
        ]);

        switch ($event) {
            case 'qrcode.status':
                $this->handleQRCodeStatus($data);
                break;

            case 'connection.status':
                $this->handleConnectionStatus($data);
                break;

            case 'device.status':
                $this->handleDeviceStatus($data);
                break;

            default:
                Log::warning('Evento desconhecido recebido:', ['event' => $event]);
        }

        return response()->json(['status' => 'ok'], 200);
    }

    private function handleQRCodeStatus(array $data)
    {
        // Ex: status = "scanned", "expired", etc.
        Log::info('📲 QRCode Status', $data);
        // Aqui você pode salvar no banco, notificar, etc.
    }

    private function handleConnectionStatus(array $data)
    {
        // Ex: status = "connected", "disconnected"
        Log::info('🌐 Conexão WhatsApp', $data);
    }

    private function handleDeviceStatus(array $data)
    {
        // Ex: status = "online", "offline", "paired"
        Log::info('📱 Status do Dispositivo', $data);
    }
}

