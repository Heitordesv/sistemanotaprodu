<?php

namespace App\Jobs;

use App\Models\ConfigNota;
use App\Models\EmpresaIntegracao;
use App\Models\MensagemPersonalizada;
use App\Models\Motoboy;
use App\Models\TelaPedidoDeli;
use App\Models\WSEmpresa;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class EnviarMensagemWhatsAppJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected int $pedidoId;
    protected int $empresaId;
    protected string $novoStatus;

    public $tries = 3;
    public $backoff = 60;

    public function __construct(int $pedidoId, int $empresaId, string $novoStatus)
    {
        $this->pedidoId = $pedidoId;
        $this->empresaId = $empresaId;
        $this->novoStatus = $novoStatus;
    }

    public function handle(): void
    {
        $pedido = TelaPedidoDeli::find($this->pedidoId);

        if (!$pedido) {
            Log::warning('Pedido não encontrado para envio de WhatsApp', [
                'pedido_id' => $this->pedidoId,
                'empresa_id' => $this->empresaId,
            ]);
            return;
        }

        $integracao = EmpresaIntegracao::where('empresa_id', $this->empresaId)
            ->where('whatsapp_provider', 'evolution')
            ->where('whatsapp_ativo', true)
            ->first();

        if (!$integracao) {
            Log::warning('Evolution não configurada/ativa para mensagens de pedido', [
                'pedido_id' => $this->pedidoId,
                'empresa_id' => $this->empresaId,
            ]);
            return;
        }

        $config = ConfigNota::where('empresa_id', $this->empresaId)->first();
        $mensagens = $config && $config->user_id
            ? MensagemPersonalizada::where('user_id', $config->user_id)
                ->where(function ($query) {
                    $query->where('status', 'ativa')
                        ->orWhere('status', 'Ativa')
                        ->orWhere('status', 1);
                })
                ->get()
                ->keyBy('tipo')
            : collect();

        $empresaLinkAvaliacao = $config && $config->user_id
            ? WSEmpresa::where('user_id', $config->user_id)->first()
            : null;

        $mensagensPadrao = [
            'Aberto' => 'Seu pedido foi recebido e está aguardando preparo.',
            'Em Andamento' => 'Seu pedido está sendo preparado! Em breve estará pronto.',
            'Saiu para Entrega' => 'Seu pedido saiu para entrega! Aguarde, logo chegará até você.',
            'Disponível para Retirada' => 'Seu pedido já está disponível para retirada no balcão!',
            'Finalizado' => 'Seu pedido foi finalizado com sucesso. Agradecemos a preferência!'
                . ($empresaLinkAvaliacao?->linkavali ? ' Avalie sua experiência: ' . $empresaLinkAvaliacao->linkavali : ''),
            'Cancelado' => 'Infelizmente, seu pedido foi cancelado. Caso tenha dúvidas, entre em contato.',
        ];

        $mensagemBase = $mensagens->get($this->novoStatus)?->mensagem
            ?? ($mensagensPadrao[$this->novoStatus] ?? null);

        if (!empty($pedido->telefone) && $mensagemBase) {
            $numeroCliente = preg_replace('/\D/', '', (string) $pedido->telefone);
            $nomeCliente = trim((string) $pedido->nome);
            $texto = $nomeCliente !== ''
                ? "Olá {$nomeCliente}, {$mensagemBase}"
                : $mensagemBase;

            SendEvolutionWhatsAppJob::dispatch(
                $this->empresaId,
                $numeroCliente,
                $texto,
                'pedido_cliente'
            );
        }

        if ($pedido->opcao_delivery && $this->novoStatus === 'Em Andamento' && $config && $config->user_id) {
            $motoboy = Motoboy::where('user_id', $config->user_id)->first();

            if ($motoboy && !empty($motoboy->deliveryman_phone_number) && !empty($pedido->cidade) && !empty($pedido->rua)) {
                $numeroMotoboy = preg_replace('/\D/', '', (string) $motoboy->deliveryman_phone_number);
                $enderecoCompleto = "{$pedido->rua}, {$pedido->unidade}, {$pedido->bairro}, {$pedido->cidade} - {$pedido->uf}"
                    . (!empty($pedido->complemento) ? ", {$pedido->complemento}" : '');
                $linkMaps = 'https://www.google.com/maps/search/?api=1&query=' . urlencode($enderecoCompleto);

                $mensagemMotoboy = "Olá {$motoboy->deliveryman_name}, você tem um novo pedido para entrega!\n\n"
                    . "📍 *Endereço:*\n{$pedido->rua}, {$pedido->unidade}\n{$pedido->bairro} - {$pedido->cidade}/{$pedido->uf}\n"
                    . (!empty($pedido->complemento) ? "Complemento: {$pedido->complemento}\n" : '')
                    . (!empty($pedido->observacao) ? "📝 Obs: {$pedido->observacao}\n" : '')
                    . "\n🔗 *Google Maps:* {$linkMaps}"
                    . "\n\n👤 *Cliente:* {$pedido->nome}\n📞 *Telefone:* {$pedido->telefone}"
                    . "\n\n💰 *Total do Pedido:* R$ " . number_format((float) $pedido->total, 2, ',', '.');

                SendEvolutionWhatsAppJob::dispatch(
                    $this->empresaId,
                    $numeroMotoboy,
                    $mensagemMotoboy,
                    'pedido_motoboy'
                );
            }
        }

        TelaPedidoDeli::where('id', $this->pedidoId)->update(['dataenvio' => now()]);
    }
}