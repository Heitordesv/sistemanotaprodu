<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use Illuminate\Http\Request;

class TicketSuperController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (!(session('user_logged')['super'] ?? false)) {
                abort(403, 'Acesso restrito ao suporte.');
            }

            return $next($request);
        });
    }

    public function index(Request $request)
    {
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json($this->chatListPayload());
        }

        $query = Ticket::with(['empresa', 'mensagens.usuario']);

        if ($request->filled('empresa')) {
            $empresa = trim($request->empresa);
            $query->whereHas('empresa', function ($q) use ($empresa) {
                $q->where('nome_fantasia', 'like', '%' . $empresa . '%')
                    ->orWhere('razao_social', 'like', '%' . $empresa . '%')
                    ->orWhere('cpf_cnpj', 'like', '%' . $empresa . '%');
            });
        }

        if ($request->filled('estado')) {
            $estado = $request->estado === 'respondido' ? 'respondida' : $request->estado;
            $query->where('estado', $estado);
        }

        if ($request->filled('departamento')) {
            $departamento = $request->departamento;
            if ($departamento === 'suporte') $departamento = '1';
            elseif ($departamento === 'conta_venda') $departamento = '2';
            $query->where('departamento', $departamento);
        }

        $data = $query
            ->orderByRaw("CASE WHEN estado = 'aberto' THEN 1 WHEN estado = 'respondida' THEN 2 ELSE 3 END")
            ->orderByDesc('updated_at')
            ->get();

        return view('ticket_super.index', compact('data'));
    }

    public function show(Request $request, $id)
    {
        $item = Ticket::with(['empresa', 'mensagens.usuario'])->findOrFail($id);

        if ($request->boolean('ajax') || $request->expectsJson()) {
            return response()->json([
                'success' => true,
                'chat' => $this->chatPayload($item),
            ]);
        }

        return view('ticket_super.show', compact('item'));
    }

    public function finalizar($id)
    {
        $item = Ticket::findOrFail($id);
        return view('ticket_super.finalizar', compact('item'));
    }

    public function finalizarPost(Request $request)
    {
        $request->validate([
            'item' => 'required|integer',
            'mensagem_finalizar' => 'nullable|string|max:200',
        ]);

        $ticket = Ticket::findOrFail($request->item);
        $ticket->mensagem_finalizar = $request->mensagem_finalizar ?: 'Atendimento finalizado pelo suporte.';
        $ticket->estado = 'finalizado';
        $ticket->save();

        session()->flash('flash_sucesso', 'Atendimento finalizado.');
        return redirect()->route('ticketsSuper.show', $ticket->id);
    }

    private function chatListPayload(): array
    {
        $tickets = Ticket::with(['empresa', 'mensagens.usuario'])
            ->orderByRaw("CASE WHEN estado = 'aberto' THEN 1 WHEN estado = 'respondida' THEN 2 ELSE 3 END")
            ->orderByDesc('updated_at')
            ->limit(100)
            ->get();

        $conversations = $tickets->map(function ($ticket) {
            $empresaNome = optional($ticket->empresa)->nome_fantasia
                ?: optional($ticket->empresa)->razao_social
                ?: ('Empresa #' . $ticket->empresa_id);

            $last = $ticket->mensagens->last();
            $preview = $last ? trim((string) $last->mensagem) : '';
            if ($preview === '' && $last && $last->imagem) $preview = 'Imagem enviada';

            $lastClient = $ticket->mensagens->filter(function ($message) {
                return !$message->mensagemSuper();
            })->last();

            $lastSupport = $ticket->mensagens->filter(function ($message) {
                return $message->mensagemSuper();
            })->last();

            $usuarioCliente = $this->nomeUsuario($lastClient, 'Cliente');
            $usuarioSuporte = $lastSupport
                ? $this->nomeUsuario($lastSupport, 'Suporte')
                : 'Aguardando atendente';

            return [
                'id' => $ticket->id,
                'empresa_id' => $ticket->empresa_id,
                'empresa_nome' => $empresaNome,
                'empresa' => $usuarioCliente . ' ↔ ' . $usuarioSuporte,
                'usuario_cliente' => $usuarioCliente,
                'usuario_suporte' => $usuarioSuporte,
                'assunto' => $empresaNome . ' · ' . $ticket->assunto,
                'departamento' => Ticket::departamentos()[$ticket->departamento] ?? $ticket->departamento,
                'estado' => $ticket->estado,
                'estado_label' => $this->estadoLabel($ticket->estado),
                'ultima_mensagem' => $preview ?: 'Sem mensagens',
                'ultima_mensagem_id' => $last ? $last->id : 0,
                'ultima_mensagem_cliente' => $last ? !$last->mensagemSuper() : false,
                'ultima_mensagem_cliente_id' => $lastClient ? $lastClient->id : 0,
                'updated_at' => optional($ticket->updated_at)->format('d/m H:i'),
                'show_url' => route('ticketsSuper.show', $ticket->id),
            ];
        })->values();

        $latestClientMessageId = 0;
        foreach ($conversations as $conversation) {
            $latestClientMessageId = max($latestClientMessageId, (int) $conversation['ultima_mensagem_cliente_id']);
        }

        return [
            'success' => true,
            'conversations' => $conversations,
            'latest_client_message_id' => $latestClientMessageId,
            'message_url' => route('tickets.novaMensagem'),
        ];
    }

    private function chatPayload(Ticket $ticket): array
    {
        $currentUserId = (int) (session('user_logged')['id'] ?? 0);

        $empresaNome = optional($ticket->empresa)->nome_fantasia
            ?: optional($ticket->empresa)->razao_social
            ?: ('Empresa #' . $ticket->empresa_id);

        $lastClient = $ticket->mensagens->filter(function ($message) {
            return !$message->mensagemSuper();
        })->last();

        $lastSupport = $ticket->mensagens->filter(function ($message) {
            return $message->mensagemSuper();
        })->last();

        $usuarioCliente = $this->nomeUsuario($lastClient, 'Cliente');
        $usuarioSuporte = $lastSupport
            ? $this->nomeUsuario($lastSupport, 'Suporte')
            : 'Aguardando atendente';

        return [
            'ticket' => [
                'id' => $ticket->id,
                'empresa_id' => $ticket->empresa_id,
                'empresa_nome' => $empresaNome,
                'empresa' => $usuarioCliente . ' ↔ ' . $usuarioSuporte,
                'usuario_cliente' => $usuarioCliente,
                'usuario_suporte' => $usuarioSuporte,
                'assunto' => $empresaNome . ' · ' . $ticket->assunto,
                'departamento' => Ticket::departamentos()[$ticket->departamento] ?? $ticket->departamento,
                'estado' => $ticket->estado,
                'estado_label' => $this->estadoLabel($ticket->estado),
                'finalizado' => $ticket->estado === 'finalizado',
                'mensagem_finalizar' => $ticket->mensagem_finalizar,
                'show_url' => route('ticketsSuper.show', $ticket->id),
                'message_url' => route('tickets.novaMensagem'),
            ],
            'mensagens' => $ticket->mensagens->map(function ($mensagem) use ($currentUserId) {
                $minhaMensagem = (int) $mensagem->usuario_id === $currentUserId;

                return [
                    'id' => $mensagem->id,
                    'imagem' => $mensagem->imagem ? asset('uploads/ticket/' . $mensagem->imagem) : null,
                    'mensagem' => (string) $mensagem->mensagem,
                    'ticket_id' => $mensagem->ticket_id,
                    'usuario_id' => $mensagem->usuario_id,
                    'usuario' => optional($mensagem->usuario)->nome ?: optional($mensagem->usuario)->login ?: 'Usuário',
                    'minha_mensagem' => $minhaMensagem,
                    'suporte_real' => $mensagem->mensagemSuper(),
                    'suporte' => $minhaMensagem,
                    'data' => optional($mensagem->created_at)->format('d/m/Y H:i'),
                ];
            })->values(),
        ];
    }

    private function nomeUsuario($mensagem, string $fallback): string
    {
        if (!$mensagem || !$mensagem->usuario) return $fallback;
        return $mensagem->usuario->nome ?: $mensagem->usuario->login ?: $fallback;
    }

    private function estadoLabel(string $estado): string
    {
        if ($estado === 'aberto') return 'Aguardando resposta';
        if ($estado === 'respondida') return 'Respondido';
        if ($estado === 'finalizado') return 'Finalizado';
        return ucfirst($estado);
    }
}