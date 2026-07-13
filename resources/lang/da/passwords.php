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

    'password' => 'Adgangskoder skal bestå af mindst seks tegn og matche bekræftelsen.',
    'reset' => 'Din adgangskode er nulstillet!',
    'sent' => 'Vi har sendt dit link til nulstilling af adgangskode via e-mail!',
    'token' => 'Dette token til nulstilling af adgangskode er ugyldigt.',
    'user' => "Vi kan ikke finde en bruger med den e-mail-adresse.",

];

return \FI\Support\TranslationOverride::override(__FILE__, $translations);
