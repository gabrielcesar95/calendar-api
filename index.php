<?php

require_once __DIR__ . '/Config.php';

$ip = $_SERVER['REMOTE_ADDR'];
echo "IP: {$ip}<br/>";

if ($ip != '189.7.154.15') {
    echo '<b>Unauthorized!</b>';
    die;
}

// Nova instância do Objeto Calendar
$calendar = new \Calendar\Calendar;

// Verificação da Autenticação
$client = $calendar->getClient(CALENDAR[TEST_ACCESS], CALENDAR[TEST_REFRESH]);

if (!$client) {
    echo "<a href=\"{$calendar->createClient()}\">Clique aqui para autorizar o acesso</a>";
} else {
    echo 'Autenticado!';
}

//GESTÃO DE CALENDÁRIOS:
//    $test = $calendar->createCalendar('Novo Calendário', 'Descrição do novo calendário', 'Piracicaba');
//    $test = $calendar->getCalendar('kjhcgipg85na6ppqf1e3dk88ag@group.calendar.google.com');
//    $test = $calendar->updateCalendar('kjhcgipg85na6ppqf1e3dk88ag@group.calendar.google.com', 'Foi pra africa', 'Foi pra africa', 'Africa', 'Africa/Accra');
//    $test = $calendar->deleteCalendar('kjhcgipg85na6ppqf1e3dk88ag@group.calendar.google.com');
//    $test = $calendar->getCalendarList();

//GESTÃO DE EVENTOS:
    $event = new \Calendar\Event();

    $test = $event->create('h08fu9b0lnjl3rg9ut102av5ic@group.calendar.google.com', 'Evento co notificações e convidados', '2018-03-11 21:00:00', '2018-03-11 22:00:00', 'Evento de teste: configurado para ter um convidado sem permissões de convidado', CALENDAR[TIMEZONE], ['email'=>2, 'popup'=>2], 'Praça José Bonifácio', ['emails'=>['sistema@gabrielcesar.info'], 'inviteOthers'=>true, 'modify'=>true, 'seeOthers'=>true]);

echo '<pre>';
print_r($test);
echo '</pre>';


//REMOÇÃO DE ACESSO DO USUÁRIO:
//$calendar->revokeToken();


// Criação de um novo evento no Google Calendar
//$event = $calendar->createEvent('Teste de Sumário', 'Rua Huberto Hoden 100, Campeche, Florianópolis', 'Teste de Descrição', '2017-12-14 18:00:00', '2017-12-14 19:00:00', 'guh.web@hotmail.com');

// Deletar o evento do Google Calendar
//$event = $calendar->deleteEvent('js3ciocb6ilu2rv5qclbqklock');

// debug
//var_dump($calendar, $client, $event);