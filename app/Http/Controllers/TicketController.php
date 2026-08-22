<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\TicketMensagem;
use App\Utils\UploadUtil;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TicketController extends Controller
{
    protected $util;

    public function __construct(UploadUtil $util)
    {
        $this->util = $util;
    }

    public function index(Request $request)
    {
        $empresaId = $this->empresaIdLogada();

        $data = Ticket::with(['empresa', 'mensagens.usuario'])
            ->where('empresa_id', $empresaId)
            ->orderByRaw("CASE WHEN estado = 'aberto' THEN 1 WHEN estado = 'respondida' THEN 2 ELSE 3 END")
            ->orderByDesc('updated_at')
            ->get();

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'conversations' => $data->map(function ($ticket) {
                    $last = $ticket->mensagens->last();
                    $preview = $last ? trim((string) $last->mensagem) : '';

                    if ($preview === '' && $last && $last->imagem) {
                        $preview = 'Imagem enviada';
                    }

                    $lastClient = $ticket->mensagens
                        ->filter(function ($message) {
                            return !$message->mensagemSuper();
                        })
                        ->last();

                    $lastSupport = $ticket->mensagens
                        ->filter(function ($message) {
                            return $message->mensagemSuper();
                        })
                        ->last();

                    $usuarioCliente = $this->nomeUsuario($lastClient, 'Usuário');
                    $usuarioSuporte = $lastSupport
                        ? $this->nomeUsuario($lastSupport, 'Suporte')
                        : 'Aguardando atendente';

                    return [
                        'id' => $ticket->id,
                        'assunto' => $ticket->assunto,
                        'departamento' => Ticket::departamentos()[$ticket->departamento] ?? $ticket->departamento,
                        'estado' => $ticket->estado,
                        'estado_label' => $this->estadoLabel($ticket->estado),
                        'usuario_cliente' => $usuarioCliente,
                        'usuario_suporte' => $usuarioSuporte,
                        'participantes' => $usuarioCliente . ' ↔ ' . $usuarioSuporte,
                        'atendente' => $usuarioSuporte,
                        'ultima_mensagem' => $preview ?: 'Sem mensagens',
                        'ultima_mensagem_id' => $last ? $last->id : 0,
                        'ultima_mensagem_suporte' => $last ? $last->mensagemSuper() : false,
                        'updated_at' => optional($ticket->updated_at)->format('d/m H:i'),
                        'show_url' => route('tickets.show', $ticket->id),
                    ];
                })->values(),
                'store_url' => route('tickets.store'),
                'message_url' => route('tickets.novaMensagem'),
            ]);
        }

        return view('ticket.index', compact('data'));
    }

    public function create()
    {
        $this->empresaIdLogada();
        return view('ticket.create');
    }

    public function store(Request $request)
    {
        $this->validateTicket($request);

        $empresaId = $this->empresaIdLogada();
        $usuarioId = $this->usuarioIdLogado();

        try {
            $fileName = '';

            if ($request->hasFile('image')) {
                $fileName = $this->util->uploadImage($request, '/ticket', 'image');
            }

            $ticket = Ticket::create([
                'empresa_id' => $empresaId,
                'estado' => 'aberto',
                'departamento' => (string) $request->departamento,
                'assunto' => trim((string) $request->assunto),
                'mensagem_finalizar' => '',
            ]);

            TicketMensagem::create([
                'imagem' => $fileName,
                'mensagem' => trim((string) $request->mensagem),
                'ticket_id' => $ticket->id,
                'usuario_id' => $usuarioId,
            ]);

            $ticket->touch();
            $ticket->load(['empresa', 'mensagens.usuario']);

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Atendimento iniciado.',
                    'chat' => $this->chatPayload($ticket),
                ], 201);
            }

            session()->flash('flash_sucesso', 'Atendimento iniciado.');
            return redirect()->route('tickets.show', $ticket->id);
        } catch (\Throwable $e) {
            Log::error('Erro ao criar ticket', [
                'empresa_id' => $empresaId,
                'usuario_id' => $usuarioId,
                'erro' => $e->getMessage(),
            ]);

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Não foi possível iniciar o atendimento.',
                    'debug' => config('app.debug') ? $e->getMessage() : null,
                ], 500);
            }

            session()->flash('flash_erro', 'Não foi possível iniciar o atendimento.');
            return redirect()->back()->withInput();
        }
    }

    public function show(Request $request, $id)
    {
        $item = $this->ticketPermitido($id)
            ->load(['empresa', 'mensagens.usuario']);

        if ($request->boolean('ajax') || $request->expectsJson()) {
            $chat = $this->chatPayload($item);

            return response()->json(array_merge([
                'success' => true,
                'chat' => $chat,
            ], $chat));
        }

        return view('ticket.show', compact('item'));
    }

    public function novaMensagem(Request $request)
    {
        $this->validateMessage($request);

        $ticket = $this->ticketPermitido($request->ticket_id);

        if ($ticket->estado === 'finalizado') {
            return $this->messageResponse($request, false, 'Este atendimento já foi finalizado.');
        }

        try {
            $fileName = '';

            if ($request->hasFile('image')) {
                $fileName = $this->util->uploadImage($request, '/ticket', 'image');
            }

            $fromSupport = $this->isSuporte();

            TicketMensagem::create([
                'imagem' => $fileName,
                'mensagem' => trim((string) $request->mensagem),
                'ticket_id' => $ticket->id,
                'usuario_id' => $this->usuarioIdLogado(),
            ]);

            $ticket->estado = $fromSupport ? 'respondida' : 'aberto';
            $ticket->save();
            $ticket->touch();
            $ticket->load(['empresa', 'mensagens.usuario']);

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Mensagem enviada.',
                    'chat' => $this->chatPayload($ticket),
                ]);
            }

            session()->flash('flash_sucesso', 'Mensagem enviada.');
            return redirect()->back();
        } catch (\Throwable $e) {
            Log::error('Erro ao enviar mensagem de ticket', [
                'ticket_id' => $ticket->id,
                'erro' => $e->getMessage(),
            ]);

            return $this->messageResponse($request, false, 'Não foi possível enviar a mensagem.');
        }
    }

    public function finalizar($id)
    {
        $item = $this->ticketPermitido($id);
        return view('ticket.finalizar', compact('item'));
    }

    public function finalizarPost(Request $request)
    {
        $ticketId = $request->ticket_id ?: $request->item;
        $ticket = $this->ticketPermitido($ticketId);

        $ticket->mensagem_finalizar = $request->mensagem_finalizar ?: 'Atendimento finalizado.';
        $ticket->estado = 'finalizado';
        $ticket->save();

        session()->flash('flash_sucesso', 'Atendimento finalizado.');
        return redirect()->route('tickets.show', $ticket->id);
    }

    private function chatPayload(Ticket $ticket): array
    {
        $currentUserId = $this->usuarioIdLogado();

        $lastClient = $ticket->mensagens
            ->filter(function ($message) {
                return !$message->mensagemSuper();
            })
            ->last();

        $lastSupport = $ticket->mensagens
            ->filter(function ($message) {
                return $message->mensagemSuper();
            })
            ->last();

        $usuarioCliente = $this->nomeUsuario($lastClient, 'Usuário');
        $usuarioSuporte = $lastSupport
            ? $this->nomeUsuario($lastSupport, 'Suporte')
            : 'Aguardando atendente';

        return [
            'ticket' => [
                'id' => $ticket->id,
                'empresa_id' => $ticket->empresa_id,
                'assunto' => $ticket->assunto,
                'departamento' => Ticket::departamentos()[$ticket->departamento] ?? $ticket->departamento,
                'estado' => $ticket->estado,
                'estado_label' => $this->estadoLabel($ticket->estado),
                'usuario_cliente' => $usuarioCliente,
                'usuario_suporte' => $usuarioSuporte,
                'participantes' => $usuarioCliente . ' ↔ ' . $usuarioSuporte,
                'atendente' => $usuarioSuporte,
                'finalizado' => $ticket->estado === 'finalizado',
                'mensagem_finalizar' => $ticket->mensagem_finalizar,
                'show_url' => route('tickets.show', $ticket->id),
            ],
            'mensagens' => $ticket->mensagens->map(function ($mensagem) use ($currentUserId) {
                $minhaMensagem = (int) $mensagem->usuario_id === (int) $currentUserId;

                return [
                    'id' => $mensagem->id,
                    'imagem' => $mensagem->imagem ? asset('uploads/ticket/' . $mensagem->imagem) : null,
                    'mensagem' => (string) $mensagem->mensagem,
                    'ticket_id' => $mensagem->ticket_id,
                    'usuario_id' => $mensagem->usuario_id,
                    'usuario' => optional($mensagem->usuario)->nome ?: optional($mensagem->usuario)->login ?: 'Usuário',
                    'minha_mensagem' => $minhaMensagem,
                    'suporte_real' => $mensagem->mensagemSuper(),
                    'suporte' => !$minhaMensagem,
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

    private function ticketPermitido($id): Ticket
    {
        $query = Ticket::query();

        if (!$this->isSuporte()) {
            $query->where('empresa_id', $this->empresaIdLogada());
        }

        return $query->findOrFail($id);
    }

    private function empresaIdLogada(): int
    {
        $session = session('user_logged');

        if (!$session || empty($session['empresa'])) {
            abort(403, 'Empresa não identificada na sessão.');
        }

        return (int) $session['empresa'];
    }

    private function usuarioIdLogado(): int
    {
        $session = session('user_logged');

        if (!$session || empty($session['id'])) {
            abort(401, 'Usuário não identificado na sessão.');
        }

        return (int) $session['id'];
    }

    private function isSuporte(): bool
    {
        return (bool) (session('user_logged')['super'] ?? false);
    }

    private function estadoLabel(string $estado): string
    {
        if ($estado === 'aberto') return 'Aguardando suporte';
        if ($estado === 'respondida') return 'Respondido';
        if ($estado === 'finalizado') return 'Finalizado';
        return ucfirst($estado);
    }

    private function validateTicket(Request $request): void
    {
        $this->validate($request, [
            'departamento' => 'required|in:1,2',
            'assunto' => 'required|string|max:100',
            'mensagem' => 'required|string|min:1|max:5000',
            'image' => 'nullable|image|max:2048',
        ]);
    }

    private function validateMessage(Request $request): void
    {
        $this->validate($request, [
            'ticket_id' => 'required|integer',
            'mensagem' => 'nullable|string|max:5000|required_without:image',
            'image' => 'nullable|image|max:2048|required_without:mensagem',
        ]);
    }

    private function messageResponse(Request $request, bool $success, string $message)
    {
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => $success,
                'message' => $message,
            ], $success ? 200 : 422);
        }

        session()->flash($success ? 'flash_sucesso' : 'flash_erro', $message);
        return redirect()->back();
    }
}