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

    'failed' => 'Šie igaliojimai neatitinka musu irašu.',
    'throttle' => 'Per daug bandymu prisijungti. Bandykite dar karta po: sekundžiu sekundžiu.',

];


return \FI\Support\TranslationOverride::override(__FILE__, $translations);
