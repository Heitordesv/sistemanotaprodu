<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessEvolutionInboundMessageJob;
use App\Models\CobrancaAgentConfig;
use App\Models\EmpresaIntegracao;
use App\Services\EvolutionApiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class EvolutionController extends Controller
{
    public function index()
    {
        return redirect()->route('dispositivos.index');
    }

    public function save(Request $request)
    {
        $empresaId = $this->empresaId();
        $current = EmpresaIntegracao::where('empresa_id', $empresaId)->first();

        $data = $request->validate([
            'base_url' => 'required|url|max:255',
            'api_key' => $current && $current->evolution_api_key ? 'nullable|string|max:4000' : 'required|string|max:4000',
            'instance_name' => 'nullable|string|max:100',
        ]);

        $values = [
            'whatsapp_provider' => 'evolution',
            'evolution_base_url' => rtrim($data['base_url'], '/'),
            'evolution_instance' => Str::slug($data['instance_name'] ?: ('nfenotas-' . $empresaId), '-'),
            'whatsapp_ativo' => true,
        ];

        if (!empty($data['api_key'])) {
            $values['evolution_api_key'] = $data['api_key'];
        }

        EmpresaIntegracao::updateOrCreate(['empresa_id' => $empresaId], $values);

        return back()->with('flash_sucesso', 'Configuração da Evolution salva.');
    }

    public function saveAgent(Request $request)
    {
        $empresaId = $this->empresaId();

        $data = $request->validate([
            'nome_agente' => 'required|string|max:100',
            'hora_envio' => 'required|date_format:H:i',
            'dias_antes' => 'nullable|string|max:100',
            'dias_atraso' => 'nullable|string|max:100',
        ]);

        CobrancaAgentConfig::updateOrCreate(
            ['empresa_id' => $empresaId],
            [
                'nome_agente' => $data['nome_agente'],
                'ativo' => $request->boolean('ativo'),
                'os_notificacoes' => $request->boolean('os_notificacoes'),
                'responder_clientes' => $request->boolean('responder_clientes'),
                'hora_envio' => $data['hora_envio'] . ':00',
                'dias_antes' => $this->parseDays($data['dias_antes'] ?? ''),
                'dias_atraso' => $this->parseDays($data['dias_atraso'] ?? ''),
            ]
        );

        EmpresaIntegracao::updateOrCreate(
            ['empresa_id' => $empresaId],
            [
                'agente_ativo' => $request->boolean('ativo'),
                'agente_cobranca' => $request->boolean('ativo'),
                'agente_ordem_servico' => $request->boolean('os_notificacoes'),
                'responder_whatsapp' => $request->boolean('responder_clientes'),
            ]
        );

        return back()->with('flash_sucesso', 'Agente atualizado.');
    }

    public function createInstance(EvolutionApiService $service)
    {
        return response()->json($service->createInstance($this->instanceOrFail()));
    }

    public function connect(EvolutionApiService $service)
    {
        return response()->json($service->connect($this->instanceOrFail()));
    }

    public function status(EvolutionApiService $service)
    {
        $instance = $this->instanceOrFail();
        $response = $service->connectionState($instance);
        $state = data_get($response, 'instance.state') ?? data_get($response, 'state');

        $instance->update([
            'evolution_status' => $state,
            'whatsapp_ativo' => $state === 'open' ? true : $instance->whatsapp_ativo,
            'whatsapp_conectado_em' => $state === 'open' ? now() : $instance->whatsapp_conectado_em,
        ]);

        return response()->json($response);
    }

    public function configureWebhook(EvolutionApiService $service)
    {
        $instance = $this->instanceOrFail();
        $secret = $instance->evolution_webhook_secret ?: Str::random(48);
        $instance->update(['evolution_webhook_secret' => $secret]);

        $url = route('evolution.webhook', [
            'empresa' => $instance->empresa_id,
        ]);

        return response()->json($service->configureWebhook($instance, $url));
    }

    public function webhook(Request $request, int $empresa)
    {
        $instance = EmpresaIntegracao::where('empresa_id', $empresa)
            ->where('whatsapp_provider', 'evolution')
            ->first();

        abort_unless($instance, 404);

        $receivedSecret = (string) $request->header('X-NFeNotas-Webhook-Secret', '');
        $expectedSecret = (string) $instance->evolution_webhook_secret;

        abort_unless(
            $receivedSecret !== ''
            && $expectedSecret !== ''
            && hash_equals($expectedSecret, $receivedSecret),
            403
        );

        $rawEvent = (string) ($request->input('event') ?: $request->input('type'));
        $event = strtoupper(str_replace(['.', '-'], '_', $rawEvent));
        $payload = $request->all();

        if ($event === 'CONNECTION_UPDATE') {
            $state = data_get($payload, 'data.state') ?? data_get($payload, 'state');
            $instance->update([
                'evolution_status' => $state,
                'whatsapp_ativo' => $state === 'open' ? true : $instance->whatsapp_ativo,
                'whatsapp_conectado_em' => $state === 'open' ? now() : $instance->whatsapp_conectado_em,
            ]);
        }

        if ($event === 'MESSAGES_UPSERT') {
            $fromMe = (bool) data_get($payload, 'data.key.fromMe', false);
            if (!$fromMe) {
                $remoteJid = (string) data_get($payload, 'data.key.remoteJid', '');
                $numero = preg_replace('/\D/', '', Str::before($remoteJid, '@'));
                $mensagem = (string) (
                    data_get($payload, 'data.message.conversation')
                    ?? data_get($payload, 'data.message.extendedTextMessage.text')
                    ?? ''
                );

                if ($numero !== '' && trim($mensagem) !== '') {
                    $providerMessageId = data_get($payload, 'data.key.id');

                    if ($providerMessageId) {
                        $duplicado = DB::table('whatsapp_message_logs')
                            ->where('empresa_id', $empresa)
                            ->where('provider_message_id', $providerMessageId)
                            ->where('direcao', 'entrada')
                            ->exists();

                        if ($duplicado) {
                            return response()->json(['ok' => true, 'duplicate' => true]);
                        }
                    }

                    DB::table('whatsapp_message_logs')->insert([
                        'empresa_id' => $empresa,
                        'tipo' => 'recebida',
                        'direcao' => 'entrada',
                        'numero' => $numero,
                        'mensagem' => $mensagem,
                        'provider' => 'evolution',
                        'provider_message_id' => $providerMessageId,
                        'status' => 'recebido',
                        'response' => json_encode($payload, JSON_UNESCAPED_UNICODE),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    ProcessEvolutionInboundMessageJob::dispatch($empresa, $numero, $mensagem);
                }
            }
        }

        return response()->json(['ok' => true]);
    }

    protected function empresaId(): int
    {
        $value = session('user_logged');
        abort_unless($value && !empty($value['empresa']), 403);
        return (int) $value['empresa'];
    }

    protected function instanceOrFail(): EmpresaIntegracao
    {
        return EmpresaIntegracao::where('empresa_id', $this->empresaId())
            ->where('whatsapp_provider', 'evolution')
            ->firstOrFail();
    }

    protected function parseDays(string $value): array
    {
        return collect(preg_split('/[,;\s]+/', $value, -1, PREG_SPLIT_NO_EMPTY))
            ->map(fn ($v) => abs((int) $v))
            ->unique()
            ->sort()
            ->values()
            ->all();
    }
}