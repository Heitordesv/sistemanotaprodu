<?php

namespace App\Http\Controllers;

use App\Models\Empresa;
use App\Services\AdminMailboxService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class EmailController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (!(session('user_logged')['super'] ?? false)) {
                abort(403, 'Acesso restrito ao administrador.');
            }

            return $next($request);
        });
    }

    /**
     * Central administrativa de e-mails.
     *
     * /emails                  -> caixa de entrada
     * /emails?tab=entrada      -> caixa de entrada
     * /emails?tab=entrada&uid=123 -> leitura de uma mensagem
     * /emails?tab=enviar       -> envio de e-mails
     */
    public function index(Request $request, AdminMailboxService $mailbox)
    {
        $tab = $request->get('tab', 'entrada');

        if ($tab !== 'enviar') {
            return $this->caixaEntrada($request, $mailbox);
        }

        $empresas = Empresa::where('status', 1)
            ->whereNotNull('email')
            ->where('email', '!=', '')
            ->orderBy('razao_social')
            ->get();

        return view('emails.index', [
            'tab' => 'enviar',
            'empresas' => $empresas,
            'emails' => [],
            'mensagemSelecionada' => null,
            'erroCaixa' => null,
            'q' => '',
            'naoLidos' => 0,
        ]);
    }

    private function caixaEntrada(Request $request, AdminMailboxService $mailbox)
    {
        $q = trim((string) $request->get('q', ''));
        $uid = (int) $request->get('uid', 0);

        try {
            if ($uid > 0) {
                return view('emails.index', [
                    'tab' => 'entrada',
                    'empresas' => collect(),
                    'emails' => [],
                    'mensagemSelecionada' => $mailbox->message($uid),
                    'erroCaixa' => null,
                    'q' => $q,
                    'naoLidos' => 0,
                ]);
            }

            $emails = $mailbox->inbox(60, $q !== '' ? $q : null);
            $naoLidos = count(array_filter(
                $emails,
                fn (array $email) => !($email['seen'] ?? false)
            ));

            return view('emails.index', [
                'tab' => 'entrada',
                'empresas' => collect(),
                'emails' => $emails,
                'mensagemSelecionada' => null,
                'erroCaixa' => null,
                'q' => $q,
                'naoLidos' => $naoLidos,
            ]);
        } catch (Throwable $e) {
            Log::error('Erro ao carregar caixa de entrada administrativa', [
                'erro' => $e->getMessage(),
                'usuario_id' => session('user_logged')['id'] ?? null,
                'mail_inbox_host' => config('mail.imap.host'),
                'mail_inbox_username' => config('mail.imap.username'),
            ]);

            return view('emails.index', [
                'tab' => 'entrada',
                'empresas' => collect(),
                'emails' => [],
                'mensagemSelecionada' => null,
                'erroCaixa' => $this->mensagemErroCaixa($e),
                'q' => $q,
                'naoLidos' => 0,
            ]);
        }
    }

    /**
     * Envia os e-mails para as empresas selecionadas.
     */
    public function enviar(Request $request)
    {
        $request->validate([
            'assunto' => 'required|string|max:255',
            'mensagem' => 'required|string',
            'empresas' => 'required|array|min:1',
        ], [
            'assunto.required' => 'Informe o assunto do e-mail.',
            'mensagem.required' => 'Digite a mensagem do e-mail.',
            'empresas.required' => 'Selecione pelo menos uma empresa.',
        ]);

        $empresas = Empresa::whereIn('id', $request->empresas)->get();

        if ($empresas->isEmpty()) {
            return back()->with('error', 'Nenhuma empresa válida selecionada.');
        }

        $enviados = 0;

        foreach ($empresas as $empresa) {
            try {
                Mail::send('emails.template', [
                    'mensagem' => $request->mensagem,
                    'empresa' => $empresa,
                ], function ($message) use ($empresa, $request) {
                    $message->to($empresa->email, $empresa->razao_social)
                        ->subject($request->assunto)
                        ->from(config('mail.from.address'), config('mail.from.name'));
                });

                Log::info("E-mail enviado para {$empresa->email} ({$empresa->razao_social})");
                $enviados++;
            } catch (Throwable $e) {
                Log::error("Erro ao enviar e-mail para {$empresa->email}: " . $e->getMessage());
            }
        }

        return redirect()
            ->route('emails.index', ['tab' => 'enviar'])
            ->with('success', "E-mails enviados com sucesso para {$enviados} empresa(s)!");
    }

    private function mensagemErroCaixa(Throwable $e): string
    {
        $message = trim($e->getMessage());

        if (str_contains($message, 'extensão PHP IMAP')) {
            return $message;
        }

        if (
            str_contains($message, 'Usuário ou senha') ||
            str_contains($message, 'MAIL_USERNAME') ||
            str_contains($message, 'MAIL_PASSWORD')
        ) {
            return 'Credenciais da caixa de entrada não configuradas. Defina MAIL_INBOX_USERNAME e MAIL_INBOX_PASSWORD no .env.';
        }

        if (
            str_contains($message, 'Servidor IMAP') ||
            str_contains($message, 'MAIL_IMAP_HOST')
        ) {
            return 'Servidor da caixa de entrada não configurado. Defina MAIL_INBOX_HOST no .env.';
        }

        if (
            str_contains($message, 'IMAP') ||
            str_contains($message, 'imap')
        ) {
            return $message;
        }

        return 'Não foi possível carregar a caixa de entrada. Consulte os logs do sistema para mais detalhes.';
    }
}