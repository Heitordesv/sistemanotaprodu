<?php

namespace App\Http\Controllers;

use App\Helpers\CobrancaMensagemHelper;
use App\Models\CobrancaAgentConfig;
use App\Models\EmpresaIntegracao;
use App\Services\EvolutionApiService;
use App\Services\GeminiAgentService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * Nome mantido por compatibilidade com as rotas antigas /dispositivos.
 * A implementação atual usa Evolution API + Gemini e não depende mais da API Brasil.
 */
class ApiBrasilController extends Controller
{
    public function index()
    {
        $empresaId = $this->empresaId();

        CobrancaMensagemHelper::garantirMensagensPadrao($empresaId);

        $integracao = EmpresaIntegracao::firstOrCreate(
            ['empresa_id' => $empresaId],
            [
                'whatsapp_provider' => 'evolution',
                'evolution_instance' => 'nfenotas-' . $empresaId,
                'ai_provider' => 'gemini',
                'gemini_model' => config('services.google_gemini.model', 'gemini-3.6-flash'),
            ]
        );

        $agent = CobrancaAgentConfig::firstOrCreate(
            ['empresa_id' => $empresaId],
            [
                'nome_agente' => 'Assistente Financeiro',
                'dias_antes' => [5, 3, 1, 0],
                'dias_atraso' => [1, 3, 7, 15, 30],
            ]
        );

        return view('api_brasil.index', compact('integracao', 'agent'));
    }

    public function store(Request $request)
    {
        $empresaId = $this->empresaId();

        $data = $request->validate([
            'evolution_instance' => 'nullable|string|max:100',
            'gemini_model' => 'nullable|string|max:100',
            'agente_instrucoes' => 'nullable|string|max:5000',
            'nome_agente' => 'nullable|string|max:100',
            'hora_envio' => 'nullable|date_format:H:i',
            'dias_antes' => 'nullable|string|max:100',
            'dias_atraso' => 'nullable|string|max:100',
        ]);

        $instanceName = $data['evolution_instance'] ?? null;
        $geminiModel = $data['gemini_model'] ?? null;
        $nomeAgente = $data['nome_agente'] ?? null;
        $horaEnvio = $data['hora_envio'] ?? null;

        $values = [
            'whatsapp_provider' => 'evolution',
            'evolution_instance' => Str::slug($instanceName ?: ('nfenotas-' . $empresaId), '-'),
            'whatsapp_ativo' => $request->boolean('whatsapp_ativo'),
            'ai_provider' => 'gemini',
            'gemini_model' => $geminiModel ?: config('services.google_gemini.model', 'gemini-3.6-flash'),
            'agente_ativo' => $request->boolean('agente_ativo'),
            'agente_cobranca' => $request->boolean('agente_cobranca'),
            'agente_ordem_servico' => $request->boolean('agente_ordem_servico'),
            'responder_whatsapp' => $request->boolean('responder_whatsapp'),
            'agente_instrucoes' => $data['agente_instrucoes'] ?? null,
        ];

        $integracao = EmpresaIntegracao::updateOrCreate(['empresa_id' => $empresaId], $values);

        CobrancaAgentConfig::updateOrCreate(
            ['empresa_id' => $empresaId],
            [
                'nome_agente' => $nomeAgente ?: 'Assistente Financeiro',
                'ativo' => $request->boolean('agente_cobranca'),
                'os_notificacoes' => $request->boolean('agente_ordem_servico'),
                'responder_clientes' => $request->boolean('responder_whatsapp'),
                'hora_envio' => ($horaEnvio ?: '09:00') . ':00',
                'dias_antes' => $this->parseDays($data['dias_antes'] ?? '5,3,1,0'),
                'dias_atraso' => $this->parseDays($data['dias_atraso'] ?? '1,3,7,15,30'),
            ]
        );

        if ($request->expectsJson()) {
            return response()->json([
                'status' => 'sucesso',
                'mensagem' => 'Configurações salvas.',
                'instance' => $integracao->evolution_instance,
            ]);
        }

        return redirect()->route('dispositivos.index')
            ->with('flash_sucesso', 'Evolution e agente atualizados com sucesso.');
    }

    public function datatables()
    {
        $integracao = EmpresaIntegracao::where('empresa_id', $this->empresaId())->first();

        if (!$integracao) {
            return response()->json(['data' => []]);
        }

        return response()->json([
            'data' => [[
                'device_name' => $integracao->evolution_instance,
                'device_token' => 'evolution',
                'number_device' => $integracao->evolution_numero,
                'service' => ['name' => 'Evolution API'],
                'created_at' => optional($integracao->created_at)->toIso8601String(),
                'status' => $integracao->evolution_status ?: 'não conectado',
                'search' => (string) $integracao->id,
            ]],
        ]);
    }

    public function start(string $device_token, EvolutionApiService $service)
    {
        $integracao = $this->integracaoOrFail();

        try {
            $response = $service->connect($integracao);
        } catch (\Throwable $connectError) {
            try {
                $service->createInstance($integracao);
                $response = $service->connect($integracao);
            } catch (\Throwable $createError) {
                return response()->json([
                    'error' => true,
                    'message' => $createError->getMessage(),
                ], 422);
            }
        }

        return response()->json($response);
    }

    public function show(string $id, EvolutionApiService $service)
    {
        $integracao = $this->integracaoOrFail();
        $status = null;

        try {
            $status = $service->connectionState($integracao);
        } catch (\Throwable $e) {
            $status = ['error' => true, 'message' => $e->getMessage()];
        }

        return response()->json([
            'id' => $integracao->id,
            'instance' => $integracao->evolution_instance,
            'base_url' => config('services.evolution.base_url'),
            'api_key_configurada' => !empty(config('services.evolution.api_key')),
            'whatsapp_ativo' => $integracao->whatsapp_ativo,
            'status' => $status,
        ]);
    }

    public function update(Request $request, string $id)
    {
        return $this->store($request);
    }

    public function destroy(Request $request, string $search)
    {
        $integracao = $this->integracaoOrFail();
        $integracao->update([
            'whatsapp_ativo' => false,
            'evolution_status' => 'desativado',
            'whatsapp_conectado_em' => null,
        ]);

        return response()->json([
            'error' => false,
            'message' => 'Conexão desativada nesta empresa.',
        ]);
    }

    public function status(EvolutionApiService $service)
    {
        $integracao = $this->integracaoOrFail();

        try {
            $response = $service->connectionState($integracao);
            $state = data_get($response, 'instance.state') ?? data_get($response, 'state');

            $integracao->update([
                'evolution_status' => $state,
                'whatsapp_ativo' => $state === 'open' ? true : $integracao->whatsapp_ativo,
                'whatsapp_conectado_em' => $state === 'open' ? now() : $integracao->whatsapp_conectado_em,
            ]);

            return response()->json([
                'status' => 'sucesso',
                'state' => $state,
                'response' => $response,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => 'erro',
                'mensagem' => $e->getMessage(),
            ], 422);
        }
    }

    public function webhook(EvolutionApiService $service)
    {
        $integracao = $this->integracaoOrFail();
        $secret = $integracao->evolution_webhook_secret ?: Str::random(48);
        $integracao->update(['evolution_webhook_secret' => $secret]);

        $url = route('evolution.webhook', [
            'empresa' => $integracao->empresa_id,
        ]);

        try {
            return response()->json([
                'status' => 'sucesso',
                'response' => $service->configureWebhook($integracao, $url),
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => 'erro',
                'mensagem' => $e->getMessage(),
            ], 422);
        }
    }

    public function googleConnect(GeminiAgentService $gemini)
    {
        try {
            return redirect()->away($gemini->authorizationUrl($this->empresaId()));
        } catch (\Throwable $e) {
            return redirect()->route('dispositivos.index')
                ->with('flash_erro', $e->getMessage());
        }
    }

    public function googleCallback(Request $request, GeminiAgentService $gemini)
    {
        $request->validate([
            'code' => 'required|string',
            'state' => 'required|string',
        ]);

        abort_unless(
            hash_equals((string) session('gemini_oauth_state'), (string) $request->state),
            403,
            'Estado OAuth inválido.'
        );

        $empresaId = (int) session('gemini_oauth_empresa');
        abort_unless($empresaId === $this->empresaId(), 403);

        $integracao = EmpresaIntegracao::firstOrCreate(
            ['empresa_id' => $empresaId],
            ['ai_provider' => 'gemini']
        );

        try {
            $gemini->exchangeAuthorizationCode($integracao, $request->code);
        } catch (\Throwable $e) {
            return redirect()->route('dispositivos.index')
                ->with('flash_erro', 'Não foi possível conectar o Gemini: ' . $e->getMessage());
        } finally {
            session()->forget(['gemini_oauth_state', 'gemini_oauth_empresa']);
        }

        return redirect()->route('dispositivos.index')
            ->with('flash_sucesso', 'Gemini conectado com sucesso.');
    }

    public function googleDisconnect()
    {
        $integracao = $this->integracaoOrFail();
        $integracao->update([
            'google_access_token' => null,
            'google_refresh_token' => null,
            'google_token_expires_at' => null,
            'agente_ativo' => false,
        ]);

        return redirect()->route('dispositivos.index')
            ->with('flash_sucesso', 'Gemini desconectado.');
    }

    private function empresaId(): int
    {
        $sessao = session('user_logged');
        abort_unless($sessao && !empty($sessao['empresa']), 403);
        return (int) $sessao['empresa'];
    }

    private function integracaoOrFail(): EmpresaIntegracao
    {
        return EmpresaIntegracao::where('empresa_id', $this->empresaId())->firstOrFail();
    }

    private function parseDays(string $value): array
    {
        return collect(preg_split('/[,;\s]+/', $value, -1, PREG_SPLIT_NO_EMPTY))
            ->map(fn ($v) => abs((int) $v))
            ->unique()
            ->sort()
            ->values()
            ->all();
    }
}