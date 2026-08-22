<?php

namespace App\Jobs;

use App\Models\ApiBrasil;
use App\Models\Cliente;
use App\Models\VendaCaixaPreVenda;
use App\Models\ItemVendaCaixaPreVenda;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class EnviarMensagemWhatsAppPreVenda implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $timeout = 30;

    protected $preVenda;
    protected $empresa;

    public function __construct(VendaCaixaPreVenda $preVenda, $empresa)
    {
        $this->preVenda = $preVenda;
        $this->empresa = $empresa;
    }

    public function handle()
    {
        try {
            $cliente = Cliente::find($this->preVenda->cliente_id);

            if (!$cliente || !$cliente->celular) {
                Log::warning("Pré-venda {$this->preVenda->id} - Cliente sem celular válido");
                return;
            }

            $numero = preg_replace('/\D/', '', $cliente->celular);
            if (strlen($numero) < 10 || strlen($numero) > 13) {
                Log::warning("Pré-venda {$this->preVenda->id} - Número inválido: {$cliente->celular}");
                return;
            }
            $numero = "55{$numero}";

            $apiBrasil = ApiBrasil::where('empresa_id', $this->empresa->id)->first();
            if (!$apiBrasil || !$apiBrasil->DeviceToken || !$apiBrasil->Bearer) {
                Log::warning("Pré-venda {$this->preVenda->id} - Configuração API Brasil ausente para empresa ID {$this->empresa->id}");
                return;
            }

            $itens = ItemVendaCaixaPreVenda::where('pre_venda_id', $this->preVenda->id)->get();
            $mensagemItens = "";
            foreach ($itens as $item) {
                $produtoNome = $item->produto->nome ?? "Produto";
                $quantidade = $item->quantidade;
                $valorUnit = number_format($item->valor, 2, ',', '.');
                $mensagemItens .= "• {$produtoNome} - Qtd: *{$quantidade}* - R$ *{$valorUnit}*\n";
            }

            $valorTotal = number_format($this->preVenda->valor_total, 2, ',', '.');

            $mensagem = "👋 Olá *{$cliente->razao_social}*!\n";
            $mensagem .= "✅ Sua pré-venda nº *{$this->preVenda->id}* foi registrada com sucesso!\n\n";
            $mensagem .= "🛒 *Itens da Pré-venda:*\n{$mensagemItens}\n";
            $mensagem .= "💰 *Valor total:* R$ *{$valorTotal}*\n\n";
            $mensagem .= "📞 Entraremos em contato em breve.\n";
            $mensagem .= "Obrigado por escolher nossa empresa! ✨";

            $response = Http::withHeaders([
                "DeviceToken"   => $apiBrasil->DeviceToken,
                "Authorization" => "Bearer {$apiBrasil->Bearer}",
            ])->post("https://gateway.apibrasil.io/api/v2/whatsapp/sendText", [
                "number" => $numero,
                "text"   => $mensagem,
            ]);

            if ($response->failed()) {
                Log::error("Erro ao enviar WhatsApp (Pré-venda {$this->preVenda->id}): " . $response->body());
            } else {
                Log::info("WhatsApp enviado com sucesso para {$cliente->celular} (Pré-venda {$this->preVenda->id}). Resposta: " . $response->body());
            }

        } catch (\Exception $e) {
            Log::error("Erro ao enviar WhatsApp da pré-venda {$this->preVenda->id}: " . $e->getMessage());
        }
    }
}
