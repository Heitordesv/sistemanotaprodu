<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ConfigNota;
use App\Models\WsConfiempresa;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class EmpresaHorarioController extends Controller
{
    /**
     * Exibe a configuração de horários de funcionamento da empresa.
     *
     * @param Request $request O objeto da requisição HTTP.
     * @return View|JsonResponse Retorna uma View com os dados da empresa ou um JsonResponse vazio.
     */
    public function index(Request $request): View|JsonResponse
    {
        // Busca a configuração da empresa com base no empresa_id
        $config = ConfigNota::where('empresa_id', $request->empresa_id)->first();

        // Se nenhuma configuração for encontrada, retorna uma resposta JSON vazia
        if (!$config) {
            return response()->json([
                'data' => [],
            ]);
        }

        // Obtém o user_id da configuração
        $userId = $config->user_id;

        // Busca os dados da empresa com base no user_id
        $empresa = WsConfiempresa::where('user_id', $userId)->first();

        // Se nenhum dado da empresa for encontrado, retorna uma resposta JSON vazia
        if (!$empresa) {
            return response()->json([
                'data' => [],
            ]);
        }

        // Passa os dados da empresa para a view
        return view('config_horario.index', ['empresa' => $empresa]);
    }

    /**
     * Atualiza a configuração de horários de funcionamento da empresa.
     *
     * @param Request $request O objeto da requisição HTTP.
     * @param int $id_empresa O ID da empresa a ser atualizada.
     * @return RedirectResponse Redireciona de volta com uma mensagem de sucesso.
     */
    public function update(Request $request, int $id_empresa): RedirectResponse
    {
        // Encontra a empresa pelo seu ID, ou lança uma exceção 404 se não for encontrada
        $empresa = WsConfiempresa::findOrFail($id_empresa);

        // Define todos os campos de configuração possíveis para os dias da semana e seus respectivos horários
        $todosCamposConfiguracao = [
            'config_segunda', 'config_terca', 'config_quarta', 'config_quinta', 'config_sexta', 'config_sabado', 'config_domingo',
            'config_segundaa', 'config_tercaa', 'config_quartaa', 'config_quintaa', 'config_sextaa', 'config_sabadoo', 'config_domingoo',
            'segunda_manha_de', 'segunda_manha_ate', 'segunda_tarde_de', 'segunda_tarde_ate',
            'terca_manha_de', 'terca_manha_ate', 'terca_tarde_de', 'terca_tarde_ate',
            'quarta_manha_de', 'quarta_manha_ate', 'quarta_tarde_de', 'quarta_tarde_ate',
            'quinta_manha_de', 'quinta_manha_ate', 'quinta_tarde_de', 'quinta_tarde_ate',
            'sexta_manha_de', 'sexta_manha_ate', 'sexta_tarde_de', 'sexta_tarde_ate',
            'sabado_manha_de', 'sabado_manha_ate', 'sabado_tarde_de', 'sabado_tarde_ate',
            'domingo_manha_de', 'domingo_manha_ate', 'domingo_tarde_de', 'domingo_tarde_ate',
        ];

        // Prepara os dados para atualização: define checkboxes desmarcados como false
        $dadosParaAtualizar = $request->only($todosCamposConfiguracao);

        foreach ($todosCamposConfiguracao as $campo) {
            // Verifica se o campo não existe no request, o que acontece com checkboxes desmarcados
            if (!isset($dadosParaAtualizar[$campo])) {
                $dadosParaAtualizar[$campo] = false;
            }
        }

        // Atualiza os horários de funcionamento da empresa
        $empresa->update($dadosParaAtualizar);

        // Redireciona de volta com uma mensagem de sucesso
        return redirect()->back()->with('success', 'Horários atualizados com sucesso!');
    }
}