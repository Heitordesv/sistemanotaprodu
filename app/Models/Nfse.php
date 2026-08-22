<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Nfse extends Model
{
    use HasFactory;

    protected $fillable = [
        'empresa_id', 'filial_id', 'valor_total', 'estado_emissao', 'serie', 'codigo_verificacao', 
        'numero_nfse', 'url_xml', 'url_pdf_nfse', 'url_pdf_rps', 'cliente_id', 'documento', 'razao_social', 
        'im', 'ie', 'cep', 'rua', 'numero', 'bairro', 'complemento', 'cidade_id', 'email', 'telefone', 
        'natureza_operacao', 'uuid'
    ];

    public static function lastNfse()
    {
        $value = session('user_logged');
        $empresa_id = $value['empresa'];

        $nfse = Nfse::where('numero_nfse', '!=', 0)
            ->where('empresa_id', $empresa_id)
            ->orderBy('numero_nfse', 'desc')
            ->first();
        return $nfse != null ? $nfse->numero_nfse : 1;
    }

    public function servico()
    {
        return $this->hasOne(NfseServico::class, 'nfse_id');
    }

    public function cidade()
    {
        return $this->belongsTo(Cidade::class, 'cidade_id');
    }

    public static function exigibilidades()
    {
        return [
            1 => 'Exígivel',
            2 => 'Não incidência',
            3 => 'Isenção',
            4 => 'Exportação',
            5 => 'Imunidade',
            6 => 'Exigibilidade Suspensa por Decisão Judicial',
            7 => 'Exigibilidade Suspensa por Processo Administrativo',
        ];
    }
}
