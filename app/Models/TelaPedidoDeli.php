<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class TelaPedidoDeli extends Model
{
    protected $table = 'ws_pedidos'; // Nome da tabela
    protected $fillable = ['status', 'view']; // Adicionado 'view' para permitir alteração

    // Adicionando o accessor para o nome
    public function getNomeAttribute($value)
    {
        return urldecode($value); // Decodifica o valor do nome antes de retornar
    }


public static function clientesInativos($userId, $dias = 30, $paginate = 100)
{
    return self::where('user_id', $userId)
        ->select(
            'telefone',
            'nome',
            'data_chart2',
            DB::raw('MAX(data) as ultima_compra'),
            DB::raw('DATEDIFF(CURDATE(), MAX(data)) as dias_desde_ultima_compra')
        )
        ->groupBy('telefone', 'nome')
        ->havingRaw('MAX(data) < DATE_SUB(CURDATE(), INTERVAL ? DAY)', [$dias])
        ->orderBy('ultima_compra', 'desc')
        ->paginate($paginate);
}


    // Método para buscar clientes VIP (mais de 10 pedidos nos últimos 45 dias)
public static function vip($userId, $dias = 60, $minPedidos = 8, $paginate = 100)
{
    return self::where('user_id', $userId)
        ->where('status', 'Finalizado') // Considerando pedidos finalizados
        ->where('data', '>=', DB::raw("DATE_SUB(NOW(), INTERVAL {$dias} DAY)")) // Correção na sintaxe do DB::raw
        ->select('telefone', 'nome', DB::raw('COUNT(*) as total_pedidos'))
        ->groupBy('telefone', 'nome')
        ->havingRaw('COUNT(*) > ?', [$minPedidos]) // Mantida a contagem correta
        ->orderBy('total_pedidos', 'desc')
        ->paginate($paginate);
}

    // Método para obter totais por status
    public static function totaisPorStatus($userId, $startDate = null, $endDate = null)
    {
        $query = DB::table('ws_pedidos')
            ->select('status', DB::raw('SUM(total) as total'))
            ->where('user_id', $userId);

        if ($startDate) {
            $query->where('data_chart2', '>=', $startDate);
        }

        if ($endDate) {
            $query->where('data_chart2', '<=', $endDate);
        }

        $result = $query->groupBy('status')->get();

        $statuses = [
            'Aberto' => 0,
            'Finalizado' => 0,
            'Cancelado' => 0,
            'Saiu para Entrega' => 0,
            'Disponível para Retirada' => 0,
            'Em Andamento' => 0,
        ];

        foreach ($result as $row) {
            if (array_key_exists($row->status, $statuses)) {
                $statuses[$row->status] = $row->total;
            }
        }

        return $statuses;
    }
}
