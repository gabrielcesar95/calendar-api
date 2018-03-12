<?php

namespace Calendar;

class Calendar {

    private $client;
    private $service;

    /**
     * Calendar constructor.
     * Método responsável por inicializar a comunicação com a API do Google
     * @throws \Google_Exception
     */
    public function __construct() {
        $this->client = new \Google_Client();
        $this->client->setApplicationName(APPLICATION_NAME);
        $this->client->setScopes(SCOPES);
        $this->client->setAuthConfig(CLIENT_SECRET_PATH);
        $this->client->setAccessType('offline');
    }

    /**
     * <b>setError:</b> Método resposável por exibir erro (front-end) e parar a execução do código, se solicitado
     * @param STRING $Message
     * @param CONSTANT(E_USER) $Type
     * @param BOOL $Die
     */
    protected function showError($Message, $Type, $Die = false) {
        $CssClass = '';

        switch ($Type) {
            case E_USER_NOTICE:
                $CssClass = CALENDAR_ERROR[INFO];
                break;
            case E_USER_WARNING:
            case E_USER_DEPRECATED:
                $CssClass = CALENDAR_ERROR[WARNING];

                break;
            case E_USER_ERROR:
                $CssClass = CALENDAR_ERROR[ERROR];

                break;
        }

        echo "<div class=\"{$CssClass}\">{$Message}</div>";

        if ($Die) {
            die;
        }
    }

    /**
     * <b>createClient:</b> Método responsável por gerar o link de autenticação da API
     * @return string
     */
    public function createClient() {
        $authUrl = $this->client->createAuthUrl();
        return $authUrl;
    }

    /**
     * <b>setAccessToken:</b> Método responsável por criar o credenciamento da API,
     * salvar o arquivo .json no diretório especificado da define
     * @param $authCode
     * @return bool
     */
    public function fetchAccessToken($authCode) {
        $accessToken = $this->client->fetchAccessTokenWithAuthCode($authCode);
        return true;
    }

    /**
     * <b>validateAuth:</b> Método responsável por verificar informação retornada pela API após o usuário permitir acesso á conta
     * @param $code (Código trazido no GET ao carregar auth.php)
     * @return bool
     */
    public function validateAuth($code) {
        $validate = $this->client->authenticate($code);
        if (isset($validate['error']) && $validate['error']) {
            unset($validate);
            return false;
        }
        unset($validate);

        return true;
    }

    /**
     * <b>revokeToken:</b> Método responsável por desassociar a conta do usuário do aplicativo
     * @param STRING $AccessToken Se omitido, remove o token do usuário autenticado em $this->client
     * @return BOOL TRUE se sucesso e FALSE se falha
     */
    public function revokeToken($AccessToken = null) {
        if (!$AccessToken) {
            $this->client->revokeToken();
        } else {
            var_dump($this->client->revokeToken($AccessToken));
        }
    }

    /**
     * <b>getClient:</b> Método responsável por obter o client do Google
     * @param STRING $AccessToken
     * access_token recebido por <i>auth.php</i> ou armazenado no banco de dados
     * @param STRING $RefreshToken
     * refresh_token recebido por <i>auth.php</i> ou armazenado no banco de dados
     * @return BOOL|\Google_Client
     */
    public function getClient($AccessToken = null, $RefreshToken = null) {
        if ($AccessToken && $RefreshToken) {
            $Tokens = [
                'access_token' => $AccessToken,
                'refresh_token' => $RefreshToken
            ];
            $this->fetchAccessToken(json_encode($Tokens));
            $this->client->setAccessToken(json_encode($Tokens));
            $this->service = new \Google_Service_Calendar($this->client);
            
            return $this->client;
        } else {
            if ($AccessToken) {
                $Tokens = [
                    'access_token' => $AccessToken,
                ];
                $this->fetchAccessToken(json_encode($Tokens));
                $this->client->setAccessToken(json_encode($Tokens));
                if (!$RefreshToken) {
                    $this->showError('Token de atualização não recebido.', E_USER_WARNING);
                }

                $this->service = new \Google_Service_Calendar($this->client);
                
                return $this->client;
            } else {
                $this->showError('Token de acesso não recebido.', E_USER_ERROR);
                return false;
            }
        }
    }

    /**
     * <b>getCalendarList:</b> Adquire todos os calendários cadastrados na conta
     * 
     * @return ARRAY - Array com todos os dados públicos dos calendários
     */
    public function getCalendarList() {

        return get_object_vars($this->service->calendarList->listCalendarList());
    }

    /**
     * <b>getCalendar:</b> Adquire todas as informações do calendário especificado
     * 
     * @param STRING $Id - ID do calendário
     * @return ARRAY - Array com os dados públicos do calendário
     */
    public function getCalendar($Id) {

        return get_object_vars($this->service->calendars->get($Id));
    }

    /**
     * <b>createCalendar:</b> Cria um calendário na conta do usuário
     * 
     * @param STRING $Name - Nome do calendário
     * @param STRING $Description - Descrição do calendário
     * @param STRING $Location - Localização do calendário
     * @param STRING $TimeZone - Fuso-horário do calendário
     * @return ARRAY - Array com os dados do calendário recém criado
     */
    public function createCalendar($Name, $Description = '', $Location = '', $TimeZone = CALENDAR[TIMEZONE]) {
        $calendar = new \Google_Service_Calendar_Calendar();

        $calendar->setSummary($Name);

        if (in_array($TimeZone, timezone_identifiers_list())) {
            $calendar->setTimeZone($TimeZone);
        }
        if ($Description) {
            $calendar->setDescription($Description);
        }
        if ($Location) {
            $calendar->setLocation($Location);
        }

        $newCalendar = $this->service->calendars->insert($calendar);

        return $this->getCalendar($newCalendar->getId());
    }

    /**
     * <b>updateCalendar:</b> Altera informações do calendário
     * 
     * @param STRING $Id - ID do calendário
     * @param STRING $Name - Nome do calendário
     * @param STRING $Description - Descrição do calendário
     * @param STRING $Location - Localização do calendário
     * @param STRING $TimeZone - Fuso-horário do calendário
     * @return ARRAY - Array com os dados do calendário recém alterado
     */
    public function updateCalendar($Id, $Name = '', $Description = '', $Location = '', $TimeZone = CALENDAR[TIMEZONE]) {
        $calendar = $this->service->calendars->get($Id);

        if ($Name) {
            $calendar->setSummary($Name);
        }
        if (in_array($TimeZone, timezone_identifiers_list())) {
            $calendar->setTimeZone($TimeZone);
        }
        if ($Description) {
            $calendar->setDescription($Description);
        }
        if ($Location) {
            $calendar->setLocation($Location);
        }

        $updatedCalendar = $this->service->calendars->update($Id, $calendar);

        return $this->getCalendar($updatedCalendar->getId());
    }

    /**
     * <b>deleteCalendar:</b> Deleta um calendário
     * 
     * @param type $Id - ID do calendário
     * @return BOOL - True se sucesso
     */
    public function deleteCalendar($Id) {
        if ($this->service->calendars->delete($Id)) {
            return true;
        }
    }


}
