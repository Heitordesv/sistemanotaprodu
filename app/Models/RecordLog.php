<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RecordLog extends Model
{
    use HasFactory;

    protected $fillable = ['tipo', 'usuario_log_id', 'tabela', 'registro_id', 'empresa_id'];

    public function usuario(){
        return $this->belongsTo(Usuario::class, 'usuario_log_id');
    }

    public function registro(){
        $registro = \DB::table($this->tabela)
        ->join('record_logs', 'record_logs.registro_id', '=', $this->tabela. ".id")
        ->where('registro_id', $this->registro_id)
        ->first();

        if($registro != null){
            if($this->tabela == 'produtos'){
                return $registro->nome;
            }
            else if($this->tabela == 'vendas'){
                return "Venda " . $registro->id . " - Cliente ID: " . $registro->cliente_id . 
                " | Total R$ " . moeda($registro->valor_total);
            }
            else if($this->tabela == 'venda_caixas'){
                return "PDV " . $registro->id . 
                " | Total R$ " . moeda($registro->valor_total);
            }
            else if($this->tabela == 'clientes'){
                return $registro->razao_social . " " . $registro->cpf_cnpj;
            }
            else{
                return $this->registro_id;
            }
        }else{
            return "Nada encontrado";
        }
    }
}
