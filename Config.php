<?php

session_start();
require __DIR__ . '/ext_lib/autoload.php';

define('BASE', '[HOSTED WEBSITE BASE]');
define('CLIENT_SECRET_PATH', __DIR__ . '/client_secret.json');
define('APPLICATION_NAME', 'Calendar Integration');
define('SCOPES', implode(' ', [\Google_Service_Calendar::CALENDAR]));

define('CALENDAR_ERROR', [
    'INFO' => 'error-info',
    'WARNING' => 'error-warning',
    'ERROR' => 'error-error',
]);

define('CALENDAR', [
    'TIMEZONE' => 'America/Sao_Paulo'
]);
