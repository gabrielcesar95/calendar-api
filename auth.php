<?php

/**
 * Created by PhpStorm.
 * User: gustavoweb
 * Date: 14/12/2017
 * Time: 17:13
 */
$code = filter_input(INPUT_GET, 'code', FILTER_DEFAULT);

session_start();

if (!empty($code)) {
    require __DIR__ . '/ext_lib/autoload.php';
    $calendar = new \Model\Calendar;
    $create = new CRUD\Create();


    $post = [
        'code' => $code,
        'client_id' => '[CLIENT ID GOES HERE]',
        'client_secret' => '[CLIENT SECRET GOES HERE]',
        'redirect_uri' => '[REDIRECT URI GOES HERE]',
        'grant_type' => 'authorization_code'
    ];

    $ch = curl_init('https://accounts.google.com/o/oauth2/token');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post);

    $response = curl_exec($ch);
    curl_close($ch);
    
    if ($calendar->validateAuth($code)) {
        //INSERIR DADOS DE ACESSO NO BANCO
        if (isset($response) && $response && !isset($response['error'])) {
            $create->create('user_tokens', array_merge(['id_user' => $_SESSION['id']], get_object_vars(json_decode($response))));
        }
    }
    header("Location: [URL TO REDIRECT USER TO]");
}