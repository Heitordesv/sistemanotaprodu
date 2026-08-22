<?php
namespace App\Services;

use ApiBrasil\Service;

class ApiBrasilService
{
    /**
     * Realiza uma chamada para a API.
     * 
     * @param string $type O tipo da API (ex: 'Auth', 'Device')
     * @param string $action A ação a ser realizada (ex: 'login', 'store')
     * @param array $params Os parâmetros da requisição
     * 
     * @return mixed
     */
    public function callApi($type, $action, $params = [])
    {
        // Adiciona o prefixo do tipo (Auth, Device, etc.) conforme necessário
        $response = Service::$type($action, $params);
        
        return $response;
    }

    /**
     * Faz o login.
     * 
     * @param string $email O e-mail do usuário
     * @param string $password A senha do usuário
     * 
     * @return mixed
     */
    public function login($email, $password)
    {
        return $this->callApi('Auth', 'login', [
            'body' => [
                'email' => $email,
                'password' => $password,
            ]
        ]);
    }

    /**
     * Faz o logout.
     * 
     * @param string $bearerToken O token de autenticação
     * 
     * @return mixed
     */
    public function logout($bearerToken)
    {
        return $this->callApi('Auth', 'logout', [
            'Bearer' => $bearerToken
        ]);
    }

    /**
     * Cria um novo dispositivo.
     * 
     * @param string $bearerToken O token de autenticação
     * @param string $secretKey A chave secreta
     * @param array $data Os dados do dispositivo
     * 
     * @return mixed
     */
    public function storeDevice($bearerToken, $secretKey, $data)
    {
        return $this->callApi('Device', 'store', [
            'Bearer' => $bearerToken,
            'SecretKey' => $secretKey,
            'body' => $data
        ]);
    }

    /**
     * Atualiza um dispositivo.
     * 
     * @param string $bearerToken O token de autenticação
     * @param array $data Os dados para atualização do dispositivo
     * 
     * @return mixed
     */
    public function updateDevice($bearerToken, $data)
    {
        return $this->callApi('Device', 'search', [
            'Bearer' => $bearerToken,
            'body' => $data
        ]);
    }

    /**
     * Exibe um dispositivo.
     * 
     * @param string $bearerToken O token de autenticação
     * @param string $search O identificador do dispositivo
     * 
     * @return mixed
     */
    public function showDevice($bearerToken, $search)
    {
        return $this->callApi('Device', 'show', [
            'Bearer' => $bearerToken,
            'method' => 'GET',
            'body' => [
                'search' => $search
            ]
        ]);
    }


    public function deleteDevice($bearerToken, $search)
    {
        return $this->callApi('Device', 'destroy', [
            'Bearer' => $bearerToken,
            'method' => 'DELETE',
            'body' => [
                'search' => $search
            ]
        ]);
    }

    public function qrcodeDevice($bearerToken, $search, $device_password)
    {
        return $this->callApi('Device', 'whatsapp/qrcode', [
            'Bearer' => $bearerToken,
            'method' => 'GET',
            'body' => [
                'device_password' => $device_password
            ]
        ]);
    }
}
