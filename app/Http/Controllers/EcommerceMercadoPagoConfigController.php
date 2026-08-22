<?php

namespace App\Http\Controllers;

use App\Models\ConfigEcommerce;
use Illuminate\Http\Request;

class EcommerceMercadoPagoConfigController extends Controller
{
    private function empresaId(): int
    {
        $sessao = session('user_logged');
        if (!$sessao) {
            abort(403);
        }

        $empresaId = is_array($sessao)
            ? (int) ($sessao['empresa_id'] ?? $sessao['empresa'] ?? 0)
            : (int) ($sessao->empresa_id ?? $sessao->empresa ?? 0);

        abort_if($empresaId <= 0, 403, 'Empresa não identificada na sessão.');

        return $empresaId;
    }

    public function index()
    {
        $empresaId = $this->empresaId();
        $config = ConfigEcommerce::where('empresa_id', $empresaId)->firstOrFail();

        return view('config_ecommerce.mercadopago_security', [
            'config' => $config,
            'webhook_url' => route('ecommerce.secure.webhook', ['configId' => $config->id]),
        ]);
    }

    public function update(Request $request)
    {
        $empresaId = $this->empresaId();
        $config = ConfigEcommerce::where('empresa_id', $empresaId)->firstOrFail();

        $data = $request->validate([
            'mercadopago_public_key' => ['required', 'string', 'max:255'],
            'mercadopago_access_token' => ['nullable', 'string', 'max:255'],
            'mercadopago_webhook_secret' => ['nullable', 'string', 'max:255'],
        ]);

        $config->mercadopago_public_key = trim($data['mercadopago_public_key']);

        if (!empty($data['mercadopago_access_token'])) {
            $config->mercadopago_access_token = trim($data['mercadopago_access_token']);
        }

        if (!empty($data['mercadopago_webhook_secret'])) {
            $config->mercadopago_webhook_secret = trim($data['mercadopago_webhook_secret']);
        }

        $config->save();

        return back()->with('flash_sucesso', 'Configuração de pagamento atualizada com segurança.');
    }
}