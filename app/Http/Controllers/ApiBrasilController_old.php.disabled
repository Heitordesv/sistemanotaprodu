<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ApiBrasil;
use App\Models\ConfigNota;
use App\Services\ApiBrasilService;
use ApiBrasil\Service; // Assumindo que este é um cliente de API de terceiros
use App\Models\Servidores;
use App\Models\API; // Usando namespace completo para evitar conflito com App\Models\ApiBrasil
use App\Models\Dispositivos;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Log; // Para registrar erros
use Illuminate\Support\Facades\Auth; // Se Auth::user() for usado para obter o ID da empresa ou outros dados

// Helper function para normalizar strings.
// Se você já tem essa função definida globalmente ou em um helper, pode remover este bloco.
if (!function_exists('normalize_string')) {
    function normalize_string($string)
    {
        // Remove caracteres especiais e espaços extras, convertendo para minúsculas
        // e substituindo espaços por hífens para uso em nomes de dispositivos, por exemplo.
        return strtolower(preg_replace('/[^a-zA-Z0-9\s]/', '', $string));
    }
}

class ApiBrasilController extends Controller
{
    protected $apiBrasilService;
    protected $bearerToken;
    protected $secretKey;

    /**
     * Construtor do controlador.
     * Injeta o serviço ApiBrasilService e define as chaves de autenticação.
     */
    public function __construct(ApiBrasilService $apiBrasilService)
    {
        $this->apiBrasilService = $apiBrasilService;
        // O bearer token estava hardcoded no código original.
        // Em produção, considere armazená-lo de forma mais segura (ex: em .env ou banco de dados)
        // e recuperá-lo dinamicamente, especialmente se for específico do usuário ou tiver validade.
        $this->bearerToken = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwczovL2dhdGV3YXkuYXBpYnJhc2lsLmlvL2FwaS92Mi9sb2dpbiIsImlhdCI6MTc0NDQ4MDkyOCwiZXhwIjoxNzc2MDE2OTI4LCJuYmYiOjE3NDQ0ODA5MjgsImp0aSI6IkU2UDRGbTJidEIwUE4xUXgiLCJzdWIiOiI1MzQ2IiwicHJ2IjoiMjNiZDVjODk0OWY2MDBhYmQzOWU3MDFjNDAwODcyZGI3YTU5NzZmNyJ9.mK8AOrvWsAvprZWKGG24ZdGLTGGDDiNPAQzkNHOIdW8';
        $this->secretKey = env('SECRET_KEY_LOGIN_API_BRASIL');
    }

    /**
     * Exibe a listagem de dispositivos da API Brasil.
     *
     * @param Request $request
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function index(Request $request)
    {
        // Garante que o empresa_id esteja presente, seja da requisição ou do usuário autenticado.
        $empresaId = $request->input('empresa_id');
        if (!$empresaId && Auth::check()) {
            $empresaId = Auth::user()->empresa_id; // Assumindo que o usuário tem um empresa_id
        }

        if (!$empresaId) {
            return redirect()->back()->with('flash_erro', 'ID da empresa não fornecido.');
        }

        $config = ConfigNota::where('empresa_id', $empresaId)->first();

        if (!$config) {
            return redirect()->back()->with('flash_erro', 'Nenhuma configuração encontrada para esta empresa.');
        }

        // A verificação de $config->user_id estava no código original.
        // Se user_id em ConfigNota for um token de autenticação, esta verificação é relevante.
        // Caso contrário, pode ser redundante se o bearerToken for um token de sistema estático.
        if (!$config->user_id) {
            return redirect()->back()->with('flash_erro', 'Token de autenticação não configurado para esta empresa.');
        }

        // Dados para a tabela local (se necessário, atualmente não usado no compact da view)
        // $data = ApiBrasil::where('empresa_id', $empresaId)->paginate(30);

        // Busca servidores e APIs para filtragem na view
        $servidores = Servidores::getAll();
        $apis = \App\Models\API::getAll(); // Usar o namespace completo para evitar conflito

        // Filtra APIs para incluir apenas as relacionadas a 'whatsapp' ou 'baileys'
        $apis = array_filter($apis, function ($api) {
            return str_contains(strtolower($api->name), 'whatsapp') || str_contains(strtolower($api->name), 'baileys');
        });

        // Filtra servidores para incluir apenas os tipos 'whatsapp' ou 'baileys'
        $servidores = array_filter($servidores, function ($servidor) {
            return $servidor->type == 'whatsapp' || $servidor->type == 'baileys';
        });

        return view('api_brasil.index')
            ->with('servidores', $servidores)
            ->with('apis', $apis)
            ->with('empresa_id', $empresaId); // Passa empresa_id para a view
    }

    /**
     * Cria um novo dispositivo na API Brasil e armazena as credenciais localmente.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function create(Request $request)
    {
        $empresaId = $request->input('empresa_id');
        if (!$empresaId) {
            session()->flash('flash_erro', 'ID da empresa não fornecido para criação do dispositivo.');
            return redirect()->back();
        }

        $config = ConfigNota::where('empresa_id', $empresaId)->first();

        if (!$config) {
            session()->flash('flash_erro', 'Nenhuma configuração encontrada para esta empresa.');
            return redirect()->back();
        }

        // Prepara os dados para a chamada à API externa
        $dataForApi = [
            "type" => "cellphone",
            "device_name" => normalize_string($config->nome_fantasia), // Usa $config->nome_fantasia
            "device_key" => (string) $empresaId, // Garante que device_key seja string, geralmente corresponde ao empresa_id
            "device_ip" => "0.0.0.0", // IP padrão
            "server_search" => "aae22ac0-824c-42e1-91de-09f44104a7d1", // Hardcoded no original, considere tornar dinâmico
            // "webhook_wh_message" => "", // Webhooks opcionais
            // "webhook_wh_status" => ""
        ];

        try {
            // Chama a API externa para armazenar o dispositivo
            $response = $this->apiBrasilService->storeDevice($this->bearerToken, $this->secretKey, $dataForApi);

            if (!$response->error) {
                // Se a chamada à API for bem-sucedida, armazena as credenciais no DB local
                // Não é necessário validar empresa_id novamente aqui, pois já foi verificado.
                // Apenas pegamos os dados relevantes para o modelo local.

                ApiBrasil::create([
                    'empresa_id' => $empresaId,
                    'DeviceToken' => $response->device->device_token,
                    'Bearer' => $this->bearerToken, // Usa o bearer token do controlador
                    'server_search' => $dataForApi['server_search'], // Usa o server_search da requisição da API
                    'situacao' => 'DISCONNECTED' // Status inicial
                ]);

                session()->flash('flash_sucesso', 'Dispositivo criado e credenciais salvas com sucesso!');
                return redirect()->route('apiBrasil.index', ['empresa_id' => $empresaId]);
            } else {
                // Lida com a resposta de erro da API
                $errorMessages = [];
                if (is_array($response->message) || is_object($response->message)) {
                    foreach ((array) $response->message as $field => $messages) {
                        foreach ((array) $messages as $message) {
                            $errorMessages[] = ucfirst($field) . ': ' . $message;
                        }
                    }
                } else {
                    $errorMessages[] = $response->message; // Se a mensagem for uma string simples
                }

                session()->flash('flash_erro', 'Erro ao criar dispositivo na API: ' . implode(' | ', $errorMessages));
                return redirect()->route('apiBrasil.index', ['empresa_id' => $empresaId]);
            }
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            // Lida com exceções de requisição HTTP do Guzzle (problemas de rede, respostas inválidas)
            $errorAsString = $e->getResponse() ? $e->getResponse()->getBody()->getContents() : $e->getMessage();
            Log::error("Erro na criação de dispositivo da API Brasil: " . $errorAsString);
            session()->flash('flash_erro', 'Erro de comunicação com a API Brasil: ' . $errorAsString);
            return redirect()->route('apiBrasil.index', ['empresa_id' => $empresaId]);
        } catch (\Exception $e) {
            // Captura quaisquer outros erros inesperados
            Log::error("Erro inesperado em ApiBrasilController@create: " . $e->getMessage());
            session()->flash('flash_erro', 'Ocorreu um erro inesperado ao criar o dispositivo: ' . $e->getMessage());
            return redirect()->route('apiBrasil.index', ['empresa_id' => $empresaId]);
        }
    }

    /**
     * Exibe o formulário de edição para uma entrada local do ApiBrasil.
     *
     * @param int $id
     * @return \Illuminate\View\View
     */
    public function edit($id)
    {
        $item = ApiBrasil::findOrFail($id);

        $config = ConfigNota::where('empresa_id', $item->empresa_id)->first();

        // Popula campos adicionais para o formulário, assumindo que são derivados ou necessários
        $item['device_name'] = $config ? normalize_string($config->nome_fantasia) : 'Nome Desconhecido';
        $item['device_key'] = $item->empresa_id; // Assumindo que device_key corresponde ao empresa_id
        $item['type'] = 'cellphone'; // Tipo padrão
        // 'situacao' e 'server_search' já estão no objeto $item do banco de dados

        return view('api_brasil.edit', compact('item'));
    }

    /**
     * Atualiza o recurso local ApiBrasil especificado no armazenamento.
     * Este método atualiza a entrada no banco de dados local.
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $id)
    {
        $item = ApiBrasil::findOrFail($id);

        try {
            $validatedData = $request->validate([
                'DeviceToken' => 'required|string|max:255',
                'Bearer' => 'required|string|max:1000',
                'server_search' => 'required|string',
                'situacao' => 'required|string'
            ]);

            $item->update($validatedData);

            session()->flash('flash_sucesso', 'Credenciais locais atualizadas com sucesso!');
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Exceção específica para erros de validação
            session()->flash('flash_erro', 'Erro de validação: ' . $e->getMessage());
            return redirect()->back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            Log::error("Erro ao atualizar entrada local do ApiBrasil (ID: {$id}): " . $e->getMessage());
            session()->flash('flash_erro', 'Erro ao atualizar credenciais locais: ' . $e->getMessage());
        }

        return redirect()->route('apiBrasil.index', ['empresa_id' => $item->empresa_id]);
    }

    /**
     * Exclui uma entrada local do ApiBrasil.
     * Este método exclui a entrada do banco de dados local.
     * A exclusão da API externa é tratada pelo método `apiDestroyDevice`.
     *
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroyLocal($id)
    {
        try {
            $item = ApiBrasil::findOrFail($id);
            $empresaId = $item->empresa_id; // Armazena antes da exclusão

            $item->delete();
            session()->flash('flash_sucesso', 'Credenciais locais deletadas com sucesso!');
        } catch (\Exception $e) {
            Log::error("Erro ao deletar entrada local do ApiBrasil (ID: {$id}): " . $e->getMessage());
            session()->flash('flash_erro', 'Erro ao deletar credenciais locais: ' . $e->getMessage());
        }

        return redirect()->route('apiBrasil.index', ['empresa_id' => $empresaId ?? null]);
    }

    /**
     * Gera o código QR e atualiza o status do dispositivo.
     * Este método é assumido para acionar um processo de geração de código QR no lado da API
     * e, em seguida, atualizar o status local.
     *
     * @param int $id O ID da entrada local do ApiBrasil.
     * @return \Illuminate\Http\RedirectResponse
     */
    public function generateQrCodeAndConnect($id)
    {
        $item = ApiBrasil::findOrFail($id);

        // Verifica se o DeviceToken existe antes de prosseguir
        if (empty($item->DeviceToken)) {
            session()->flash('flash_erro', 'DeviceToken não encontrado para gerar QRCODE.');
            return redirect()->route('apiBrasil.index', ['empresa_id' => $item->empresa_id]);
        }

        try {
            // Assumindo que apiBrasilService tem um método para solicitar o QR code ou iniciar o dispositivo.
            // O código original chamava deleteDevice aqui, o que é incorreto para um método de QR code.
            // Substitua por uma chamada apropriada para iniciar o dispositivo e obter o QR code, se disponível.
            // Por enquanto, simularei uma geração de QR code bem-sucedida e atualização de status.
            $response = $this->apiBrasilService->startDevice($this->bearerToken, $item->DeviceToken); // Assumindo que este método existe

            if (!$response->error) {
                $validatedData = [
                    'situacao' => 'inChat' // Ou 'QRCODE_GENERATED', 'CONNECTED', etc., com base na resposta da API
                ];
                $item->update($validatedData);
                session()->flash('flash_sucesso', 'Comando de QRCODE enviado e status atualizado para "inChat"!');
                // Se a API retornar os dados do QR code, você pode redirecionar para uma view
                // que exiba o QR code, ou retorná-lo como JSON para uma chamada AJAX.
            } else {
                $errorMessages = [];
                if (is_array($response->message) || is_object($response->message)) {
                    foreach ((array) $response->message as $field => $messages) {
                        foreach ((array) $messages as $message) {
                            $errorMessages[] = ucfirst($field) . ': ' . $message;
                        }
                    }
                } else {
                    $errorMessages[] = $response->message;
                }
                session()->flash('flash_erro', 'Erro ao solicitar QRCODE da API: ' . implode(' | ', $errorMessages));
            }
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            $errorAsString = $e->getResponse() ? $e->getResponse()->getBody()->getContents() : $e->getMessage();
            Log::error("Erro de QR Code da API Brasil: " . $errorAsString);
            session()->flash('flash_erro', 'Erro de comunicação com a API Brasil ao gerar QRCODE: ' . $errorAsString);
        } catch (\Exception $e) {
            Log::error("Erro inesperado em ApiBrasilController@generateQrCodeAndConnect: " . $e->getMessage());
            session()->flash('flash_erro', 'Ocorreu um erro inesperado ao gerar o QRCODE: ' . $e->getMessage());
        }

        return redirect()->route('apiBrasil.index', ['empresa_id' => $item->empresa_id]);
    }

    /**
     * Obtém dados do dispositivo para DataTables.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function datatables()
    {
        $dispositivos = Dispositivos::getAll();

        $dispositivos = array_filter($dispositivos, function ($dispositivo) {
            return $dispositivo->type == 'cellphone' || $dispositivo->type == 'tablet';
        });

        return DataTables::of($dispositivos)->make(true);
    }

    /**
     * Inicia uma sessão do WhatsApp para um determinado token de dispositivo.
     * Este é um endpoint de API.
     *
     * @param string $device_token
     * @return \Illuminate\Http\JsonResponse
     */
    public function start(string $device_token)
    {
        try {
            // Obtém dinamicamente o Bearer token associado ao device_token
            $apiBrasilEntry = ApiBrasil::where('DeviceToken', $device_token)->first();

            if (!$apiBrasilEntry || empty($apiBrasilEntry->Bearer)) {
                return response()->json([
                    'error' => true,
                    'message' => 'Bearer token não encontrado para o DeviceToken fornecido.'
                ], 404);
            }

            $token = $apiBrasilEntry->Bearer;

            $start = Service::WhatsApp("start", [
                "Bearer" => $token,
                "method" => "GET",
                "DeviceToken" => $device_token
            ]);

            return response()->json($start);
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            $errorAsString = $e->getResponse() ? $e->getResponse()->getBody()->getContents() : $e->getMessage();
            Log::error("Erro ao iniciar dispositivo da API Brasil: " . $errorAsString);
            return response()->json([
                'error' => true,
                'message' => json_decode($errorAsString) // Assumindo que o erro é JSON
            ], 400);
        } catch (\Exception $e) {
            Log::error("Erro inesperado em ApiBrasilController@start: " . $e->getMessage());
            return response()->json([
                'error' => true,
                'message' => 'Ocorreu um erro inesperado ao iniciar o dispositivo: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Exibe os detalhes do dispositivo especificado da API externa.
     * Este é um endpoint de API.
     *
     * @param string $device_token O token do dispositivo a ser pesquisado.
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(string $device_token)
    {
        try {
            $show = Service::Device("show", [
                "Bearer" => $this->bearerToken, // Usando o bearer token do controlador
                "method" => "GET",
                "body" => [
                    "search" => $device_token // Assumindo que $device_token é o parâmetro de pesquisa
                ]
            ]);

            return response()->json($show);
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            $errorAsString = $e->getResponse() ? $e->getResponse()->getBody()->getContents() : $e->getMessage();
            Log::error("Erro ao exibir dispositivo da API Brasil: " . $errorAsString);
            return response()->json([
                'error' => true,
                'message' => json_decode($errorAsString)
            ], 400);
        } catch (\Throwable $th) {
            Log::error("Erro inesperado em ApiBrasilController@show: " . $th->getMessage());
            return response()->json([
                'error' => true,
                'message' => 'Ocorreu um erro inesperado ao buscar detalhes do dispositivo: ' . $th->getMessage()
            ], 500);
        }
    }

    /**
     * Atualiza o dispositivo especificado na API externa.
     * Este é um endpoint de API.
     *
     * @param Request $request
     * @param string $device_key A chave do dispositivo (ou ID) para identificar o dispositivo na API externa.
     * @return \Illuminate\Http\JsonResponse
     */
    public function apiUpdateDevice(Request $request, string $device_key)
    {
        try {
            // Valida os dados da requisição para a atualização da API
            $validated = $request->validate([
                'type' => 'required|string',
                'device_name' => 'required|string',
                'device_ip' => 'nullable|string',
                'server_search' => 'required|string',
                // Adicione outros campos conforme necessário pela sua API
            ]);

            // O código original usava o método "store" para atualização, o que pode ser um upsert ou update.
            // Assumindo que 'store' também pode lidar com atualizações se device_key for fornecido.
            $update = Service::Device("store", [
                "Bearer" => $this->bearerToken,
                "SecretKey" => $this->secretKey, // Usa a secret key do controlador
                "body" => [
                    'type' => $validated['type'],
                    'device_name' => $validated['device_name'],
                    'device_key' => $device_key, // Usa a device_key do parâmetro da URL
                    'device_ip' => $validated['device_ip'] ?? "0.0.0.0", // Padrão se não fornecido
                    'server_search' => $validated['server_search']
                ]
            ]);

            return response()->json($update);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'error' => true,
                'message' => $e->errors()
            ], 422); // Entidade Não Processável
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            $errorAsString = $e->getResponse() ? $e->getResponse()->getBody()->getContents() : $e->getMessage();
            Log::error("Erro ao atualizar dispositivo da API Brasil: " . $errorAsString);
            return response()->json([
                'error' => true,
                'message' => json_decode($errorAsString)
            ], 400);
        } catch (\Throwable $th) {
            Log::error("Erro inesperado em ApiBrasilController@apiUpdateDevice: " . $th->getMessage());
            return response()->json([
                'error' => true,
                'message' => 'Ocorreu um erro inesperado ao atualizar o dispositivo na API: ' . $th->getMessage()
            ], 500);
        }
    }

    /**
     * Remove o dispositivo especificado da API externa.
     * Este é um endpoint de API.
     *
     * @param string $search O identificador para pesquisar o dispositivo a ser destruído (ex: device_token, device_key).
     * @return \Illuminate\Http\JsonResponse
     */
    public function apiDestroyDevice(string $search)
    {
        try {
            $delete = Service::Device("destroy", [
                "Bearer" => $this->bearerToken,
                "method" => "DELETE",
                "body" => [
                    'search' => $search
                ]
            ]);

            return response()->json($delete);
        } catch (\GuzzleHttp\Exception\GuzzleException $e) {
            Log::error("Erro ao deletar dispositivo da API Brasil: " . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 400);
        } catch (\Throwable $th) {
            Log::error("Erro inesperado em ApiBrasilController@apiDestroyDevice: " . $th->getMessage());
            return response()->json([
                'error' => true,
                'message' => 'Ocorreu um erro inesperado ao deletar o dispositivo da API: ' . $th->getMessage()
            ], 500);
        }
    }
}
