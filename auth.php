<?php

$code = filter_input(INPUT_GET, 'code', FILTER_DEFAULT);

session_start();

if (!empty($code)) {
    require __DIR__ . '/Config.php';
    $calendar = new \Calendar\Calendar;

    $post = [
        'code' => $code,
        'client_id' => CALENDAR[CLIENT_ID],
        'client_secret' => CALENDAR[CLIENT_SECRET],
        'redirect_uri' => CALENDAR[REDIRECT_URI],
        'grant_type' => 'authorization_code'
    ];

    $ch = curl_init('https://accounts.google.com/o/oauth2/token');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post);

    $response = curl_exec($ch);
    curl_close($ch);

    if (!$calendar->validateAuth($code)) {
        $calendar->showError('Falha ao validar parâmetros GET', E_USER_ERROR, true);
    }
    
header("Location: " . CALENDAR[AUTH_REDIRECT]);
}