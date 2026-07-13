<?php

$translations = [

    /*
    |--------------------------------------------------------------------------
    | Password Reminder Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines are the default lines which match reasons
    | that are given by the password broker for a password update attempt
    | has failed, such as for an invalid token or invalid new password.
    |
    */

    'password' => 'Le password devono contenere almeno sei caratteri e la conferma password deve combaciare.',
    'user' => "Non abbiamo trovato un utente con questo indirizzo email.",
    'token' => 'Questo token per il reset della password non è valido.',
    'sent' => 'Ti abbiamo inviato il link per reimpostare la password via email!',
    'reset' => 'La tua password è stata reimpostata!',

];

return \FI\Support\TranslationOverride::override(__FILE__, $translations);
