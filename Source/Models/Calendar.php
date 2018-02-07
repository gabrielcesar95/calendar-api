<?php

/**
 * Created by PhpStorm.
 * User: gustavoweb
 * Date: 14/12/2017
 * Time: 16:27
 */

namespace Model;

class Calendar {

    private $client;
    private $trigger;
    private $event;
    private $params;
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
     * <b>getTrigger:</b> Método responsável por retornar mensagem de erro
     * @return mixed
     */
    public function getTrigger() {
        return $this->trigger;
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
    public function setAccessToken($authCode) {
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
            //enviar email para o adm sobre o erro

            unset($validate);
            return false;
        } elseif (!isset($validate['refresh_roken'])) {
            //exibir erro (Acesso disponivel apenas por uma hora)
        }
        unset($validate);
        return true;
    }

    /**
     * <b>getClient:</b> Método responsável por obter o client do Google
     * @return bool|\Google_Client
     */
    public function getClient() {
        $error = new \stdClass();

        //PEGAR ID DO USUARIO NA SESSAO
        $userId = $_SESSION['id'];


        if (isset($userId) && $userId) {
            $read = new \CRUD\Read();
            $read->read('user_tokens', 'WHERE id_user = :id', "id={$userId}");
            if ($read->getRowCount()) {
                $userauth = $read->getResult()[0];
                unset($userauth['id']);
                unset($userauth['id_user']);

                $accessToken = $this->client->fetchAccessTokenWithAuthCode(json_encode($userauth));

                $this->client->setAccessToken(json_encode($userauth));
                return $this->client;
            } else {
                $error->error = [
                    'code' => 2,
                    'link' => $this->client->createAuthUrl()
                ];
                return $error;
            }
        } else {
            $error->error = [
                'code' => 1,
                'link' => $this->client->createAuthUrl()
            ];

            return $error;
        }
    }

    /**
     * <b>createEvent:</b> Método responsável por criar o evento dentro do Google Calendar
     * @param STRING $summary = Título do Evento
     * @param STRING $location = Endereço completo do onde ocorrerá o evento
     * @param STRING $description = Descrição do evento
     * @param DATETIME $start = Data e Hora no formato americano
     * @param DATETIME $end = Data e Hora no formato americano
     * @param null|STRING $attendees = E-mail do Convidado
     * @return bool|\Google_Service_Calendar_Event
     */
    public function createEvent($summary, $location, $description, $start, $end, $attendees = null) {
        if (date('Y-m-d H:i:s', strtotime($start)) < date('Y-m-d H:i:s')) {
            $this->trigger = "A data inicial é menor do que a data atual, por favor verifique e tente novamente!";
            return false;
        }

        if (date('Y-m-d H:i:s', strtotime($end)) < date('Y-m-d H:i:s', strtotime($start))) {
            $this->trigger = "A data final é menor que a data de início, por favor verifique e tente novamente!";
            return false;
        }

        $this->params = array(
            'summary' => $summary,
            'location' => $location,
            'description' => $description,
            'start' => array(
                'dateTime' => date(DATE_ISO8601, strtotime($start)),
                'timeZone' => 'America/Sao_Paulo',
            ),
            'end' => array(
                'dateTime' => date(DATE_ISO8601, strtotime($end)),
                'timeZone' => 'America/Sao_Paulo',
            ),
            'reminders' => array(
                'useDefault' => false,
                'overrides' => array(
                    array('method' => 'email', 'minutes' => 24 * 60),
                    array('method' => 'popup', 'minutes' => 10),
                ),
            ),
        );

        if (!empty($attendees)) {
            if (filter_var($attendees, FILTER_VALIDATE_EMAIL)) {
                $this->params += [
                    'attendees' => array(
                        array('email' => $attendees),
                    ),
                ];
            }
        }

        $this->event = new \Google_Service_Calendar_Event($this->params);
        $this->service = new \Google_Service_Calendar($this->client);
        $this->event = $this->service->events->insert('primary', $this->event, ['sendNotifications' => true]);
        return $this->event;
    }

    /**
     * <b>deleteEvent:</b> Método responsável por deletar um evento do Google Calendar
     * @param STRING $eventId = ID do evento do Google
     */
    public function deleteEvent($eventId) {
        $this->service = new \Google_Service_Calendar($this->client);
        $this->service->events->delete('primary', $eventId, ['sendNotifications' => true]);
    }

}
