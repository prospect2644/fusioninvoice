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

    'password' => 'Slaptažodžiai turi buti bent šešiu simboliu ir atitikti patvirtinima.',
    'reset' => 'Jusu slaptažodis buvo nustatytas iš naujo!',
    'sent' => 'Mes atsiunteme jusu slaptažodžio nustatymo nuoroda el. Paštu!',
    'token' => 'Šis slaptažodžio nustatymo atpažinimo ženklas yra netinkamas.',
    'user' => "Mes negalime rasti vartotojo su tuo el. Pašto adresu.",

];

return \FI\Support\TranslationOverride::override(__FILE__, $translations);
