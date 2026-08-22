<?php

namespace App\Helpers;

use App\Models\ConfigNota;
use App\Models\MensagemPersonalizada;
use Carbon\Carbon;

class CobrancaMensagemHelper
{
    public static function antesVencimento(): string
    {
        return "Olá, {cliente}! 😊\n\nPassando para lembrar que você possui uma conta no valor de *R$ {valor}*, com vencimento em *{vencimento}*.\n\nPara facilitar, você pode realizar o pagamento pelo link abaixo:\n{link}\n\nReferência: {referencia}\n\nSe você já realizou o pagamento, pode desconsiderar esta mensagem.\n\nAtenciosamente,\n{agente}";
    }

    public static function venceHoje(): string
    {
        return "Olá, {cliente}! 👋\n\nSua conta no valor de *R$ {valor} vence hoje ({vencimento})*.\n\nVocê pode acessar o link abaixo para realizar o pagamento:\n{link}\n\nReferência: {referencia}\n\nCaso o pagamento já tenha sido realizado, desconsidere esta mensagem.\n\nAtenciosamente,\n{agente}";
    }

    public static function emAtraso(): string
    {
        return "Olá, {cliente}! Tudo bem?\n\nIdentificamos que a conta no valor de *R$ {valor}*, com vencimento em *{vencimento}*, ainda consta como pendente em nosso sistema.\n\nPara regularizar, acesse:\n{link}\n\nReferência: {referencia}\n\nSe você já efetuou o pagamento, responda *JÁ PAGUEI* para que possamos orientar a conferência.\n\nSe precisar de ajuda, pode responder esta mensagem.\n\nAtenciosamente,\n{agente}";
    }

    public static function templatesPadrao(): array
    {
        return [
            'Cobranca Antes' => self::antesVencimento(),
            'Cobranca Hoje' => self::venceHoje(),
            'Cobranca Atraso' => self::emAtraso(),
            'OS Pendente' => "Olá, {cliente}! 👋\n\nRecebemos sua *Ordem de Serviço #{os}* e ela está aguardando atendimento.\n\nVamos avisar você sempre que houver uma atualização.",
            'OS Em Andamento' => "Olá, {cliente}! 🔧\n\nSua *Ordem de Serviço #{os}* está *em andamento*. Nossa equipe já está trabalhando no atendimento.\n\nAvisaremos assim que houver uma nova atualização.",
            'OS Pronto' => "Olá, {cliente}! ✅\n\nBoa notícia! Sua *Ordem de Serviço #{os}* está *pronta*.\n\nVocê já pode entrar em contato conosco para combinar a retirada ou entrega.",
            'OS Finalizado' => "Olá, {cliente}! 🎉\n\nSua *Ordem de Serviço #{os}* foi *finalizada*.\n\nAgradecemos pela confiança em nosso trabalho!",
            'OS Reprovado' => "Olá, {cliente}!\n\nA *Ordem de Serviço #{os}* foi atualizada para *reprovada*.\n\nSe tiver alguma dúvida, responda esta mensagem para falar conosco.",
        ];
    }

    /**
     * Cria os templates padrão no cadastro já existente sem sobrescrever
     * nenhuma mensagem que a empresa tenha personalizado.
     */
    public static function garantirMensagensPadrao(int $empresaId): void
    {
        $config = ConfigNota::where('empresa_id', $empresaId)->first();
        if (!$config || !$config->user_id) {
            return;
        }

        foreach (self::templatesPadrao() as $tipo => $mensagem) {
            MensagemPersonalizada::firstOrCreate(
                [
                    'user_id' => $config->user_id,
                    'tipo' => $tipo,
                ],
                [
                    'mensagem' => $mensagem,
                    'status' => 'ativa',
                ]
            );
        }
    }

    public static function montarCobranca($config, $conta, $cliente): string
    {
        $nome = $cliente->razao_social ?: ($cliente->nome_fantasia ?: ($cliente->nome ?? 'cliente'));
        $saldo = max(0, (float) $conta->valor_integral - (float) $conta->valor_recebido);
        $valor = number_format($saldo, 2, ',', '.');
        $vencimentoCarbon = Carbon::parse($conta->data_vencimento)->startOfDay();
        $vencimento = $vencimentoCarbon->format('d/m/Y');
        $dias = now()->startOfDay()->diffInDays($vencimentoCarbon, false);

        // Contas de clientes usam /pg/{id}; contas ligadas a outra empresa
        // por empresa_id_emp usam a página específica /pgempresa/{id}.
        $rotaPagamento = !empty($conta->empresa_id_emp) ? '/pgempresa/' : '/pg/';
        $link = rtrim(config('app.url'), '/') . $rotaPagamento . $conta->id;

        $agente = optional($config)->nome_agente ?: 'Assistente Financeiro';

        if ($dias > 0) {
            $tipo = 'Cobranca Antes';
        } elseif ($dias === 0) {
            $tipo = 'Cobranca Hoje';
        } else {
            $tipo = 'Cobranca Atraso';
        }

        $template = self::mensagemCadastrada((int) $conta->empresa_id, $tipo)
            ?: self::templatesPadrao()[$tipo];

        return strtr($template, [
            '{cliente}' => $nome,
            '{valor}' => $valor,
            '{vencimento}' => $vencimento,
            '{link}' => $link,
            '{referencia}' => (string) ($conta->referencia ?: 'Não informada'),
            '{agente}' => $agente,
            '{conta_id}' => (string) $conta->id,
        ]);
    }

    public static function osAtualizacao($ordem, $cliente, ?string $textoPersonalizado = null): string
    {
        $nome = $cliente->razao_social ?: ($cliente->nome_fantasia ?: ($cliente->nome ?? 'cliente'));
        $numero = $ordem->numero_sequencial ?: $ordem->id;
        $estado = mb_strtolower(trim((string) $ordem->estado));

        $tipos = [
            'pendente' => 'OS Pendente',
            'em andamento' => 'OS Em Andamento',
            'pronto' => 'OS Pronto',
            'finalizado' => 'OS Finalizado',
            'reprovado' => 'OS Reprovado',
        ];

        $template = null;
        $tipo = $tipos[$estado] ?? null;

        if ($textoPersonalizado !== null && trim($textoPersonalizado) !== '') {
            $template = trim($textoPersonalizado);
        } elseif ($tipo) {
            $template = self::mensagemCadastrada((int) $ordem->empresa_id, $tipo);
        }

        $template = $template
            ?: ($tipo && isset(self::templatesPadrao()[$tipo])
                ? self::templatesPadrao()[$tipo]
                : "Olá, {cliente}! 👋\n\nSua *Ordem de Serviço #{os}* teve uma atualização.\n\nStatus atual: *{status}*.");

        return strtr($template, [
            '{cliente}' => $nome,
            '{os}' => (string) $numero,
            '{os_id}' => (string) $ordem->id,
            '{status}' => (string) $ordem->estado,
            '{descricao}' => (string) ($ordem->descricao ?: ''),
            '{valor}' => number_format((float) ($ordem->valor ?: 0), 2, ',', '.'),
        ]);
    }

    public static function mensagemCadastrada(int $empresaId, string $tipo): ?string
    {
        $config = ConfigNota::where('empresa_id', $empresaId)->first();
        if (!$config || !$config->user_id) {
            return null;
        }

        $mensagem = MensagemPersonalizada::where('user_id', $config->user_id)
            ->where('tipo', $tipo)
            ->where(function ($query) {
                $query->where('status', 'ativa')
                    ->orWhere('status', 'Ativa')
                    ->orWhere('status', 1);
            })
            ->orderByDesc('id')
            ->first();

        return $mensagem?->mensagem;
    }
}