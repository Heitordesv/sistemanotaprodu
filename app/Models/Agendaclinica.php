<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Agendaclinica extends Model
{
    use HasFactory;
    
    // Nome da tabela no banco de dados
    protected $table = 'agendaclinica';

    // Campos que podem ser preenchidos em massa (mass assignable)
    protected $fillable = [
        'cliente_id',
        'servico_id',
        'profissional_id',
        'data_agendamento',
        'hora_inicio',
        'hora_fim',
        'status',
        'observacao',
        'empresa_id', // Se o agendamento for diretamente ligado à empresa
    ];

    // Casts para conversão de tipos de dados
    protected $casts = [
        'data_agendamento' => 'date',
    ];

    // Relacionamentos:

    public function cliente()
    {
        // Um agendamento pertence a um cliente
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }

    public function servico()
    {
        // Um agendamento é referente a um serviço
        return $this->belongsTo(Servico::class, 'servico_id');
    }

 	public function Funcionario(){
		return $this->belongsTo(Funcionario::class, 'funcionario_id');
	}
}