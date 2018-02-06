<?php
$read = new CRUD\Read;
$calendar = new Model\Calendar;

$client = $calendar->getClient();

//EXIBICAO DO LINK CASO NAO AUTORIZADO
if ($client->error) {
    switch ($client->error['code']) {
        case 1:
        case 2:
            echo "<a href='{$client->error['link']}'>Clique aqui para autorizar o acesso a sua conta Google</a>";
            break;
    }
} else {
    $service = new Google_Service_Calendar($client);
}

//////////////////////LISTAGEM DE EVENTOS//////////////////////

//echo '<pre>';
////print_r($service->calendarList->listCalendarList());
//print_r($service->calendarList->get('hehvj7usksn4dm1opdkh6a09tc@group.calendar.google.com'));
//echo '</pre>';

//ID DO CALENDARIO DE TRABALHO:
//hehvj7usksn4dm1opdkh6a09tc@group.calendar.google.com

//$events = $service->events->listEvents('hehvj7usksn4dm1opdkh6a09tc@group.calendar.google.com', ['maxResults' => 2]);

//echo '<pre>';
//print_r($events);
//echo '</pre>';

//foreach ($events->getItems() as $event) {
//    echo $event->getSummary() . '<br/>';
//}
?>
<form class="main_form" action="add_appointment" method="post" enctype="multipart/form-data">
    <label class="label">
        <span>Título do Agendamento:</span>
        <input name="appointment_title" type="text" class="font_large" placeholder="O que gostaria de agendar?" required>
    </label>

    <label class="label">
        <span>Descrição:</span>
        <textarea rows="4" name="appointment_description"></textarea>
    </label>

    <div class="form_row_50">
        <label class="label_50">
            <span>Localização:</span>
            <input name="appointment_location" type="text" placeholder="Onde será?">
        </label>

        <label class="label_50">
            <span>E-mail:</span>
            <input name="appointment_email" type="email" placeholder="Qual o e-mail do participante?">
        </label>
    </div>

    <label class="label">
        <span>Selecione um dia e horário:</span>
        <select name="appointment_schedule_id">
            <option selected disabled value="">Nenhum Selecionado</option>
            <?php
            $read->readFull('SELECT * FROM schedule s WHERE s.schedule_status IS NULL AND s.schedule_tstamp >= CURRENT_TIMESTAMP ORDER BY s.schedule_tstamp ASC');
            $schedules = ($read->getResult()) ? $read->getResult() : null;

            if (!empty($schedules)) {
                foreach ($schedules as $schedule) {
                    $schedule = (object) $schedule;
                    echo "<option value ='{$schedule->schedule_id}'>" . date('d/m/Y H:i', strtotime($schedule->schedule_tstamp)) . "</option>";
                }
            }
            ?>
        </select>
    </label>

    <p>
        <button type="submit" class="btn">Marcar Consulta!</button>
    </p>
</form>