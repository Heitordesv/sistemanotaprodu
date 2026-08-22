<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;

class WhatsAppAiMessageController extends Controller
{
    public function index()
    {
        $sessao = session('user_logged');
        abort_unless($sessao && !empty($sessao['empresa']), 403);

        $empresaId = (int) $sessao['empresa'];

        $mensagens = DB::table('whatsapp_message_logs')
            ->where('empresa_id', $empresaId)
            ->where('direcao', 'saida')
            ->whereIn('tipo', [
                'cobranca',
                'cobranca_manual',
                'ordem_servico',
                'ordem_servico_manual',
                'agente_resposta',
            ])
            ->orderByDesc('id')
            ->limit(100)
            ->get();

        return view('api_brasil.mensagens_ia', compact('mensagens'));
    }
}