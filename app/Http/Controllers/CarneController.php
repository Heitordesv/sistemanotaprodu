<?php

namespace App\Http\Controllers;

use App\Models\ConfigNota;
use App\Models\Venda;
use App\Models\VendaCaixa;
use Dompdf\Dompdf;
use Illuminate\Http\Request;

class CarneController extends Controller
{
    public function index(Request $request)
    {
        if (isset($request->tipo_venda)) {
            $venda = VendaCaixa::find($request->id);
            if ($venda->cliente_id == null) {
                session()->flash("mensagem_erro", "Venda sem cliente!");
                return redirect()->back();
            }
        } else {
            $venda = Venda::find($request->id);
        }

        $juros = __convert_value_bd($request->juros);
        $multa = __convert_value_bd($request->multa);
        $config = ConfigNota::where('empresa_id', $venda->empresa_id)
            ->first();

        if (!$juros) {
            $juros = $config->juro_padrao;
        }

        if (!$multa) {
            $multa = $config->multa_padrao;
        }

        if (isset($request->tipo_venda)) {
            foreach ($venda->fatura as $dp) {
                $dp->juros = $juros;
                $dp->multa = $multa;
            }
        } else {
            foreach ($venda->duplicatas as $dp) {
                $dp->juros = $juros;
                $dp->multa = $multa;
                $dp->save();
            }
        }

        $valorJuros = $venda->valor_total * $juros / 100;
        $valorMulta = $venda->valor_total * $multa / 100;

        if (isset($request->tipo_venda)) {
            $p = view('frontBox.carne')
                ->with('valorJuros', $valorJuros)
                ->with('valorMulta', $valorMulta)
                ->with('config', $config)
                ->with('venda', $venda);
        } else {
            $p = view('vendas.carne')
                ->with('valorJuros', $valorJuros)
                ->with('valorMulta', $valorMulta)
                ->with('config', $config)
                ->with('venda', $venda);
        }

        // return $p;

        $domPdf = new Dompdf(["enable_remote" => true]);
        $domPdf->loadHtml($p);

        $pdf = ob_get_clean();

        $domPdf->setPaper("A4");
        $domPdf->render();
        $domPdf->stream("Carnê de venda.pdf", array("Attachment" => false));
    }
}
