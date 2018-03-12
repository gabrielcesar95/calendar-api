<?php

namespace Calendar;

class Event extends Calendar {

    public $Event;

    function __construct() {
        parent::__construct();
    }

    /**
     * <b>createEvent:</b> Método responsável por criar o evento dentro do Google Calendar
     * @param STRING $CalendarId = ID do calendário onde o evento será criado
     * @param STRING $Name = Título do Evento
     * @param STRING $Description = Descrição do evento
     * @param DATETIME $Start = Data e Hora no formato americano
     * @param DATETIME $End = Data e Hora no formato americano
     * @param ARRAY $Reminders = Array de tipos/minutos de notificação. Valores aceitos: 'email', 'popup'. Para definir tempo de notificação, passar o método como chave e o tempo como value
     * Ex: ['email'=>30] - para definir tempo, ou ['email'] para usar o tempo padrão (definido em CALENDAR[EMAIL_REMINDER_TIME])
     * @param STRING $Location = Endereço completo do local do evento
     * @param ARRAY $Attendees = Array de e-mails dos convidados
     * @return bool|\Google_Service_Calendar_Event
     */
    public function create($CalendarId, $Name, $Start, $End, string $Description = null, string $TimeZone = CALENDAR[TIMEZONE], array $Reminders = null, string $Location = null, array $Attendees = null, $SendNotifications = false) {
        $Params = [
            'summary' => $Name,
            'description' => $Description,
            'start' => [
                'dateTime' => date(DATE_ISO8601, strtotime($Start)),
                'timeZone' => $TimeZone,
            ],
            'end' => [
                'dateTime' => date(DATE_ISO8601, strtotime($End)),
                'timeZone' => $TimeZone,
            ],
        ];

        if (date('Y-m-d H:i:s', strtotime($Start)) < date('Y-m-d H:i:s')) {
            $this->showError("A data inicial é menor do que a data atual, por favor verifique e tente novamente!", E_USER_WARNING);
            return false;
        }

        if (date('Y-m-d H:i:s', strtotime($End)) < date('Y-m-d H:i:s', strtotime($Start))) {
            $this->showError("A data final é menor que a data de início, por favor verifique e tente novamente!", E_USER_WARNING);
            return false;
        }

        if ($Location) {
            $Params['location'] = $Location;
        }

        if ($Reminders) {
            $Params['reminders'] = [
                'useDefault' => false,
                'overrides' => []
            ];

            if (in_array('email', $Reminders)) {
                $Params['reminders']['overrides'][] = ['method' => 'email', 'minutes' => CALENDAR[EMAIL_REMINDER_TIME]];
            }
            if (in_array('popup', $Reminders)) {
                $Params['reminders']['overrides'][] = ['method' => 'popup', 'minutes' => CALENDAR[POPUP_REMINDER_TIME]];
            }

            if (array_key_exists('email', $Reminders) && is_int($Reminders['email'])) {
                $Params['reminders']['overrides'][] = ['method' => 'email', 'minutes' => $Reminders['email']];
            }
            if (array_key_exists('popup', $Reminders) && is_int($Reminders['popup'])) {
                $Params['reminders']['overrides'][] = ['method' => 'popup', 'minutes' => $Reminders['popup']];
            }
        }

        if (!empty($Attendees)) {
            $Params['attendees'] = [];
            $Params['guestsCanInviteOthers'] = false;
            $Params['guestsCanSeeOtherGuests'] = false;

            if ($Attendees['inviteOthers']) {
                $Params['guestsCanInviteOthers'] = true;
            }

            if ($Attendees['modify']) {
                $Params['guestsCanModify'] = true;
            }

            if ($Attendees['seeOthers']) {
                $Params['guestsCanSeeOtherGuests'] = true;
            }

            if ($Attendees['emails']) {
                foreach ($Attendees['emails'] as $Attendee) {
                    if (filter_var($Attendee, FILTER_VALIDATE_EMAIL)) {
                        $Params['attendees'][] = ['email' => $Attendee];
                    }
                }
            }
        }

        $Event = new \Google_Service_Calendar_Event($Params);
        $newEvent = $this->service->events->insert($CalendarId, $Event, ['sendNotifications' => (($SendNotifications) ? true : false)]);
        return $newEvent;
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
