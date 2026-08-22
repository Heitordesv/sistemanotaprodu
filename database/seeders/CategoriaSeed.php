<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\CategoriaConta;
use App\Models\FormaPagamento;

class CategoriaSeed extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        CategoriaConta::create([
            'nome' => 'Compras',
            'empresa_id' => 1,
            'tipo' => 'pagar'
        ]);
        CategoriaConta::create([
            'nome' => 'Vendas',
            'empresa_id' => 1,
            'tipo' => 'receber'
        ]);

        FormaPagamento::create([
            'empresa_id' => 1,
            'nome' => 'A vista',
            'chave' => 'a_vista',
            'taxa' => 0,
            'status' => 1,
            'prazo_dias' => 0,
            'tipo_taxa' => 'perc'
        ]);
        FormaPagamento::create([
            'empresa_id' => 1,
            'nome' => '30 dias',
            'chave' => '30_dias',
            'taxa' => 0,
            'status' => 1,
            'prazo_dias' => 30,
            'tipo_taxa' => 'perc'
        ]);
        FormaPagamento::create([
            'empresa_id' => 1,
            'nome' => 'Personalizado',
            'chave' => 'personalizado',
            'taxa' => 0,
            'status' => 1,
            'prazo_dias' => 0,
            'tipo_taxa' => 'perc'
        ]);
        FormaPagamento::create([
            'empresa_id' => 1,
            'nome' => 'Conta crediario',
            'chave' => 'conta_crediario',
            'taxa' => 0,
            'status' => 1,
            'prazo_dias' => 0,
            'tipo_taxa' => 'perc'
        ]);
    }
}
