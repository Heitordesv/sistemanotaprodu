<?php

namespace App\Jobs;

use App\Models\ApiBrasil;
use App\Models\Cliente;
use App\Models\CupomDescontoEcommerce;
use App\Models\VendaCaixa;
use App\Models\ItemVendaCaixa;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class EnviarMensagemWhatsAppVenda implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $timeout = 30;

    protected $vendaId;
    protected $empresaId;

    public function __construct(VendaCaixa $venda, $empresa)
    {
        // A fila deve transportar somente escalares. Modelos podem conter textos
        // legados que impedem a serializacao JSON do payload do job.
        $this->vendaId = (int) $venda->id;
        $this->empresaId = (int) $empresa->id;
    }

    public function handle()
    {
        try {
            $venda = VendaCaixa::find($this->vendaId);

            if (!$venda) {
                Log::warning('WhatsApp sale skipped: sale not found.', [
                    'sale_id' => $this->vendaId,
                ]);
                return;
            }

            $cliente = Cliente::find($venda->cliente_id);

            // CORREÇÃO E VERIFICAÇÃO PRINCIPAL: Não executa se o cliente não existe ou não tem celular.
            if (!$cliente || !$cliente->celular) {
                Log::warning("Venda {$venda->id} - Cliente sem celular válido, a mensagem não será enviada.");
                return; // Interrompe a execução do job
            }

            // Limpa o número de celular (deixa apenas dígitos)
            $numero = preg_replace('/\D/', '', $cliente->celular);
            
            // Verifica o tamanho do número limpo e interrompe se for inválido
            if (strlen($numero) < 10 || strlen($numero) > 13) {
                Log::warning("Venda {$venda->id} - Número de celular ({$numero}) com tamanho inválido, a mensagem não será enviada.");
                return; // Interrompe a execução do job
            }

            // Adiciona o código do país (55 - Brasil)
            $numero = "55{$numero}";

            $apiBrasil = ApiBrasil::where('empresa_id', $this->empresaId)->first();
            if (!$apiBrasil || !$apiBrasil->DeviceToken || !$apiBrasil->Bearer) {
                Log::error("Venda {$venda->id} - Configuração da API Brasil ausente, a mensagem não será enviada.");
                return; // Interrompe se as credenciais da API estiverem ausentes
            }

            // Monta mensagem detalhada dos itens
            $itens = ItemVendaCaixa::where('venda_caixa_id', $venda->id)->get();
            $mensagemItens = "";
            foreach ($itens as $item) {
                // Checa se o produto existe antes de acessar a propriedade 'nome'
                $produtoNome = $item->produto ? $item->produto->nome : "Produto Não Encontrado";
                $quantidade = $item->quantidade;
                $valorUnit = number_format($item->valor, 2, ',', '.');
                $mensagemItens .= "• {$produtoNome} - Qtd: *{$quantidade}* - R$ *{$valorUnit}*\n";
            }

            $valorTotal = number_format($venda->valor_total, 2, ',', '.');

            // Verifica se existe cupom ativo para a empresa
            $cupom = CupomDescontoEcommerce::where('empresa_id', $this->empresaId)
                ->where('status', 1) // somente ativos
                ->first();

            $mensagemCupom = "";
            if ($cupom) {
                $mensagemCupom = "\n🎁 *Cupom de Desconto Disponível!*\n";
                $mensagemCupom .= "Descrição: {$cupom->descricao}\n";
                $mensagemCupom .= "Código: *{$cupom->codigo}*\n";

                // Formata o valor dependendo do tipo
                if ($cupom->tipo === 'percentual') {
                    $valorCupom = number_format($cupom->valor, 2, ',', '.') . '%';
                } else { // fixo
                    $valorCupom = 'R$ ' . number_format($cupom->valor, 2, ',', '.');
                }

                $mensagemCupom .= "Tipo: {$cupom->tipo} - Valor: {$valorCupom}\n";
                $mensagemCupom .= "Valor mínimo para pedidos: R$ " . number_format($cupom->valor_minimo_pedido, 2, ',', '.') . "\n";
            }

            // Mensagem final
            $mensagem = "👋 Olá *{$cliente->razao_social}*!\n";
            $mensagem .= "✅ Recebemos sua compra nº *{$venda->id}* com sucesso!\n\n";
            $mensagem .= "🛒 *Itens adquiridos:*\n{$mensagemItens}\n";
            $mensagem .= "💰 *Total da compra:* R$ *{$valorTotal}*\n";
            $mensagem .= $mensagemCupom;
            $mensagem .= "✨ Muito obrigado por comprar conosco!";

            // Envia mensagem via API
            Http::withHeaders([
                "DeviceToken"   => $apiBrasil->DeviceToken,
                "Authorization" => "Bearer {$apiBrasil->Bearer}",
            ])->post("https://gateway.apibrasil.io/api/v2/whatsapp/sendText", [
                "number" => $numero,
                "text"  => $mensagem,
            ]);

        } catch (\Throwable $e) {
            Log::error('WhatsApp sale job failed.', [
                'sale_id' => $this->vendaId,
                'exception' => get_class($e),
            ]);
        }
    }
}