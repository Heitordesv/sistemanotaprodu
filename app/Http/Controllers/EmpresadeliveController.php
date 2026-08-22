<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ConfigNota;
use App\Models\WsConfiempresa;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage; // Still useful for deleting old files if they were in Storage::disk('public')
use Illuminate\Support\Str; // To generate unique file names

class EmpresadeliveController extends Controller
{
    /**
     * Exibe a configuração da empresa ou retorna JSON se não encontrada.
     *
     * @param Request $request O objeto da requisição HTTP.
     * @return View|JsonResponse
     */
    public function index(Request $request): View|JsonResponse
    {
        $config = ConfigNota::where('empresa_id', $request->empresa_id)->first();

        if (!$config) {
            return response()->json([
                'data' => [],
                'message' => 'Configuração não encontrada para esta empresa.'
            ]);
        }

        $userId = $config->user_id;
        $empresa = WsConfiempresa::where('user_id', $userId)->first();

        if (!$empresa) {
             abort(404, 'Empresa não encontrada para o usuário especificado.');
        }

        return view('configu_delivery.index', compact('empresa'));
    }

    /**
     * Exibe o formulário de edição para uma empresa específica.
     *
     * @param int $id O ID da empresa.
     * @return View
     */
    public function edit(int $id): View
    {
        $empresa = WsConfiempresa::where('id_empresa', $id)->firstOrFail();

        return view('configu_delivery.edit', compact('empresa'));
    }

    /**
     * Atualiza os dados de uma empresa existente.
     *
     * @param Request $request O objeto da requisição HTTP.
     * @param int $id O ID da empresa a ser atualizada.
     * @return RedirectResponse
     */
     public function update(Request $request, int $id): RedirectResponse
    {
        $empresa = WsConfiempresa::findOrFail($id);

        $data = $this->validateRequest($request);

        // --- Lidar com upload de imagens diretamente em public/delivery/ ---
        $deliveryPublicPath = public_path('delivery');

        // Certifica-se de que os diretórios existam
        if (!file_exists($deliveryPublicPath . '/headers')) {
            mkdir($deliveryPublicPath . '/headers', 0777, true);
        }
        if (!file_exists($deliveryPublicPath . '/logos')) {
            mkdir($deliveryPublicPath . '/logos', 0777, true);
        }

        if ($request->hasFile('img_header')) {
            $file = $request->file('img_header');
            $fileName = Str::uuid() . '.' . $file->getClientOriginalExtension(); // Gera um nome de arquivo único
            $path = 'delivery/headers/' . $fileName; // Caminho a ser salvo no DB

            // Move o arquivo para o diretório público
            $file->move($deliveryPublicPath . '/headers', $fileName);

            // Exclui a imagem antiga se existir (se o caminho estiver correto)
            if ($empresa->img_header && file_exists(public_path($empresa->img_header))) {
                unlink(public_path($empresa->img_header));
            }
            $data['img_header'] = $path;
        }

        if ($request->hasFile('img_logo')) {
            $file = $request->file('img_logo');
            $fileName = Str::uuid() . '.' . $file->getClientOriginalExtension(); // Gera um nome de arquivo único
            $path = 'delivery/logos/' . $fileName; // Caminho a ser salvo no DB

            // Move o arquivo para o diretório público
            $file->move($deliveryPublicPath . '/logos', $fileName);

            // Exclui a imagem antiga se existir (se o caminho estiver correto)
            if ($empresa->img_logo && file_exists(public_path($empresa->img_logo))) {
                unlink(public_path($empresa->img_logo));
            }
            $data['img_logo'] = $path;
        }

        // Convert currency inputs from Brazilian format (e.g., "1.234,56") to a float (e.g., 1234.56)
        // This conversion should happen AFTER validation, or you need to ensure validation handles strings correctly.
        // If 'config_delivery' and 'minimo_delivery' are validated as 'string' then 'numeric',
        // perform the conversion after validation if you expect a string like "1.234,56".
        // If you change the validation to 'numeric' directly, Laravel's validator might handle it,
        // but it's safer to convert it before saving to the database if the input is a formatted string.
        $data['config_delivery'] = str_replace(['.', ','], ['', '.'], $data['config_delivery']);
        $data['minimo_delivery'] = str_replace(['.', ','], ['', '.'], $data['minimo_delivery']);


        $empresa->update($data);

        return redirect()->back()->with('success', 'Empresa atualizada com sucesso!');
    }

    /**
     * Valida os dados da requisição do formulário.
     *
     * @param Request $request O objeto da requisição HTTP.
     * @return array Os dados validados.
     */
    private function validateRequest(Request $request): array
    {
        return $request->validate([
            'nome_empresa'           => 'required|string|max:255',
            'descricao_empresa'      => 'required|string|max:297',
            'telefone_empresa'       => 'required|string|max:20',
            'email_empresa'          => 'required|email|max:255',
            'layout'                 => 'required|in:0,1,2',
            'ativatele'              => 'required|in:0,1',

            'facebook_status'        => 'required|in:1,2',
            'facebook_empresa'       => 'nullable|url|max:255',
            'instagram_status'       => 'required|in:1,2',
            'instagram_empresa'      => 'nullable|url|max:255',
            'twitter_status'         => 'required|in:1,2',
            'twitter_empresa'        => 'nullable|url|max:255',

            'img_header'             => 'nullable|image|max:2048',
            'img_logo'               => 'nullable|image|max:2048',

            'confirm_delivery'       => 'nullable|in:true,false',
            'confirm_balcao'         => 'nullable|in:true,false',
            'confirm_mesa'           => 'nullable|in:true,false',

            'qtcach'                 => 'nullable|numeric',
            'valorcach'              => 'nullable|string|max:20',
            'cupom'                  => 'nullable|string|max:100',

            // New fields for color and delivery settings
            'cor_topo'               => 'required|string|max:7', // For hex color codes like #RRGGBB
            'cor_titulo_produtos'    => 'required|string|max:7',
            'cor_loading'            => 'required|string|max:7',
            'config_delivery'        => 'required|string|max:20', // Keep as string for currency format
            'minimo_delivery'        => 'required|string|max:20', // Keep as string for currency format
            'msg_tempo_delivery'     => 'required|string|max:255',
            'msg_tempo_buscar'       => 'required|string|max:255',
            'data_agendada'               => 'required|in:0,1',

            'type_pay'               => 'required|in:0,1',
        ]);
    }
}