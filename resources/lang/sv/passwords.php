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

    'password' => 'Lösenord måste vara minst sex tecken och matcha bekräftelsen.',
    'user' => "Vi kan inte hitta en användare med den e-postadressen.",
    'token' => 'Denna lösenordsåterställnings kod är ogiltig.',
    'sent' => 'Vi har e-postat din länk för återställning av lösenord!',
    'reset' => 'Ditt lösenord har blivit återställt!',

];

return \FI\Support\TranslationOverride::override(__FILE__, $translations);
