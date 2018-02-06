<?php
/**
 * Created by PhpStorm.
 * User: gustavoweb
 * Date: 20/12/2017
 * Time: 17:37
 */

define('DATABASE', [
    'HOST' => '[DATABASE HOST]',
    'PORT' => '[DATABASE PORT]',
    'USER' => '[DATABASE USER]',
    'PASS' => '[DATABASE PASSWORD]',
    'NAME' => '[DATABASE NAME]'
]);
define('BASE', '[BASE PATH]');

session_start();
$_SESSION['id'] = session_id();

require __DIR__ . '/ext_lib/autoload.php';