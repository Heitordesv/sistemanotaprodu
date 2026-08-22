<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TicketMensagem extends Model
{
    use HasFactory;

    protected $fillable = [
        'imagem',
        'mensagem',
        'ticket_id',
        'usuario_id',
    ];

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }

    public function ticket()
    {
        return $this->belongsTo(Ticket::class, 'ticket_id');
    }

    /**
     * Identifica se a mensagem veio do suporte.
     *
     * A primeira regra continua respeitando USERMASTER/isSuper().
     * Quando a empresa do ticket e informada, usuarios que nao pertencem
     * a empresa cliente tambem sao tratados como suporte. Isso evita que
     * mensagens do atendimento caiam no mesmo lado das mensagens do cliente.
     */
    public function mensagemSuper($empresaId = null): bool
    {
        if (!$this->usuario) {
            return false;
        }

        if (isSuper($this->usuario->login)) {
            return true;
        }

        if ($empresaId !== null && $this->usuario->empresa_id !== null) {
            return (int) $this->usuario->empresa_id !== (int) $empresaId;
        }

        return false;
    }
}