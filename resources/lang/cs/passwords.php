<?php

$translations = [

    /*
    |--------------------------------------------------------------------------
    | Password Reset Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines are the default lines which match reasons
    | that are given by the password broker for a password update attempt
    | has failed, such as for an invalid token or invalid new password.
    |
    */

    'password' => 'Hesla musí mít alespon šest znaku a musí odpovídat potvrzení.',
    'reset' => 'Vaše heslo bylo resetováno!',
    'sent' => 'Váš odkaz pro resetování hesla jsme zaslali e-mailem!',
    'token' => 'Tento token pro resetování hesla je neplatný.',
    'user' => "Nemužeme najít uživatele s touto e-mailovou adresou.",

];

return \FI\Support\TranslationOverride::override(__FILE__, $translations);
