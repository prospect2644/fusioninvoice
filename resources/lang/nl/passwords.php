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

    'password' => 'Wachtwoorden moeten minimaal zes karakters bevatten en overeenkomen met het bevestigings wachtwoord.',
    'user' => "Er is geen gebruiker bekend met het opgegeven e-mail adres.",
    'token' => 'De token voor uw wachtwoord reset is ongeldig.',
    'sent' => 'De link voor uw wachtwoord reset is gemaild!',
    'reset' => 'Uw wachtwoord is gereset!',

];

return \FI\Support\TranslationOverride::override(__FILE__, $translations);
