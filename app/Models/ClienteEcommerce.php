<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;

class ClienteEcommerce extends Model
{
    protected $fillable = [
        'nome', 'sobre_nome', 'cpf', 'email', 'senha', 'status', 'empresa_id',
        'telefone', 'ie', 'token'
    ];

    protected $hidden = [
        'senha', 'token'
    ];

    public function enderecos()
    {
        return $this->hasMany(EnderecoEcommerce::class, 'cliente_id', 'id');
    }

    public function pedidos()
    {
        return PedidoEcommerce::where('cliente_id', $this->id)
            ->where('empresa_id', $this->empresa_id)
            ->where('valor_total', '>', 0)
            ->orderBy('id', 'desc')
            ->get();
    }

    public function senhaConfere(string $senha): bool
    {
        $hashAtual = (string) $this->senha;

        if ($hashAtual === '') {
            return false;
        }

        if ($this->senhaEhLegadaMd5($hashAtual)) {
            $valida = hash_equals($hashAtual, md5($senha));

            if ($valida) {
                $this->forceFill(['senha' => Hash::make($senha)])->save();
            }

            return $valida;
        }

        return Hash::check($senha, $hashAtual);
    }

    private function senhaEhLegadaMd5(string $hash): bool
    {
        return (bool) preg_match('/^[a-f0-9]{32}$/i', $hash);
    }
}