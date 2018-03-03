<?php
session_start();
require __DIR__ . '/ext_lib/autoload.php';

define('BASE', 'https://www.gabrielcesar.info.com');
define('CLIENT_SECRET_PATH', __DIR__ . '/client_secret.json');
define('APPLICATION_NAME', 'Calendar Integration');
define('SCOPES', implode(' ', [\Google_Service_Calendar::CALENDAR]));

define('CALENDAR_ERROR', [
    'INFO'=>'error-info',
    'WARNING'=>'error-warning',
    'ERROR'=>'error-error',
]);

