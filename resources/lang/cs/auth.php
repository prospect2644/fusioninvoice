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

    'failed' => 'Tato poverení neodpovídají našim záznamum.',
    'throttle' => 'Príliš mnoho pokusu o prihlášení. Zkuste to znovu za: sekund.',

];


return \FI\Support\TranslationOverride::override(__FILE__, $translations);
