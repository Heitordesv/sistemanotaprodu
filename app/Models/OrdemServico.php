<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class OrdemServico extends Model
{

    protected $fillable = [
        'descricao', 'cliente_id', 'usuario_id', 'empresa_id', 'valor','estado', 'valor_pago', 'status_pagamento'
    ];

    public function servicos(){
        return $this->hasMany('App\Models\ServicoOs', 'ordem_servico_id', 'id');
    }

    public function produtos(){
        return $this->hasMany(ProdutoOs::class, 'ordem_servico_id', 'id');
    }

    public function relatorios(){
        return $this->hasMany('App\Models\RelatorioOs', 'ordem_servico_id', 'id');
    }

    public function funcionarios(){
        return $this->hasMany('App\Models\FuncionarioOs', 'ordem_servico_id', 'id');
    }

    public function cliente(){
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }

    public function usuario(){
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }

    public static function filtroData($dataInicial, $dataFinal, $estado){
        $value = session('user_logged');
        $empresa_id = $value['empresa'];
        $c = OrdemServico::
        whereBetween('data_vencimento', [$dataInicial, 
            $dataFinal])
        ->where('ordem_servicos.empresa_id', $empresa_id)
        ->where('estado', $estado);

        return $c->get();
    }
    public static function filtroDataFornecedor($cliente, $dataInicial, $dataFinal, $estado){

        $value = session('user_logged');
        $empresa_id = $value['empresa'];
        $c = OrdemServico::
        join('clientes', 'clientes.id' , '=', 'ordem_servicos.cliente_id')
        ->where('razao_social.nome', 'LIKE', "%$cliente%")
        ->whereBetween('data_vencimento', [$dataInicial, 
            $dataFinal])
        ->where('ordem_servicos.empresa_id', $empresa_id)
        ->where('estado', $estado);
        return $c->get();
    }

    public static function filtroCliente($cliente, $estado){
        $value = session('user_logged');
        $empresa_id = $value['empresa'];
        $c = OrdemServico::
        join('clientes', 'clientes.id' , '=', 'ordem_servicos.cliente_id')
        ->where('razao_social', 'LIKE', "%$cliente%")
        ->where('ordem_servicos.empresa_id', $empresa_id)
        ->where('estado', $estado);
        
        return $c->get();
    }
    
}
