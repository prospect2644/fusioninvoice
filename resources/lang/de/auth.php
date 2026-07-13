<?php

$translations = [

    /*
    |--------------------------------------------------------------------------
    | Authentication Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines are used during authentication for various
    | messages that we need to display to the user. You are free to modify
    | these language lines according to your application's requirements.
    |
    */

    'failed' => 'Diese Anmeldedaten stimmen nicht mit unseren Aufzeichnungen &uuml;berein.',
    'throttle' => 'Zu viele Anmeldeversuche. Bitte versuchen Sie es noch einmal in :seconds Sekunden.',

];

return \FI\Support\TranslationOverride::override(__FILE__, $translations);
