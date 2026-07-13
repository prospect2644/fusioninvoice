<?php

$translations = [

    /*
    |--------------------------------------------------------------------------
    | Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines contain the default error messages used by
    | the validator class. Some of these rules have multiple versions such
    | as the size rules. Feel free to tweak each of these messages here.
    |
    */

    'accepted'                => ':attribute turi buti priimtas.',
    'active_url'              => ':attribute nera tinkamas URL.',
    'after'                   => ':attribute turi buti data po :date.',
    'after_or_equal'          => ':attribute turi buti data, lygi arba lygi :date.',
    'alpha'                   => ':attribute gali buti tik raides.',
    'alpha_dash'              => ':attribute gali buti tik raides, skaiciai ir brukšniai.',
    'alpha_num'               => ':attribute gali buti tik raides ir skaiciai.',
    'array'                   => ':attribute turi buti masyvas.',
    'before'                  => ':attribute turi buti data prieš :date.',
    'before_or_equal'         => ':attribute turi buti data, kuri yra ankstesne arba lygi :date.',
    'between' => [
        'numeric'             => ':attribute turi buti tarp :min ir :max.',
        'file'                => ':attribute turi buti tarp :min ir :max kilobaitu.',
        'string'              => ':attribute turi buti tarp :min ir :max simboliu.',
        'array'               => ':attribute turi buti tarp :min ir :max elementu.',
    ],
    'boolean'                 => 'Laukas :attribute turi buti teisingas arba klaidingas.',
    'confirmed'               => ':attribute patvirtinimas nesutampa.',
    'date'                    => ':attribute nera tinkama data.',
    'date_format'             => ':attribute neatitinka :format. formato',
    'different'               => ':attribute ir :other turi skirtis.',
    'digits'                  => ':attribute turi buti :digits skaitmenu.',
    'digits_between'          => ':attribute turi buti tarp :min ir :max skaitmenu.',
    'dimensions'              => ':attribute turi netinkamus vaizdo matmenis.',
    'distinct'                => 'Lauke :attribute yra pasikartojanti verte.',
    'email'                   => ':attribute turi buti galiojantis el. Pašto adresas.',
    'exists'                  => 'Pasirinkta :attribute yra neteisinga.',
    'file'                    => ':attribute turi buti failas.',
    'filled'                  => 'Laukas :attribute turi tureti verte.',
    'image'                   => ':attribute turi buti vaizdas.',
    'in'                      => 'Pasirinkta :attribute yra neteisinga.',
    'in_array'                => ':attribute laukas neegzistuoja :other.',
    'integer'                 => ':attribute turi buti sveikas skaicius.',
    'ip'                      => ':attribute turi buti galiojantis IP adresas.',
    'ipv4'                    => ':attribute turi buti galiojantis IPv4 adresas.',
    'ipv6'                    => ':attribute turi buti galiojantis IPv6 adresas.',
    'json'                    => ':attribute turi buti galiojanti JSON eilute.',
    'max' => [
        'numeric'             => ':attribute negali buti didesnis nei :max.',
        'file'                => ':attribute negali buti didesnis nei :max kilobaitai.',
        'string'              => ':attribute negali buti didesnis nei :max simboliai.',
        'array'               => ':attribute gali buti ne daugiau kaip :max elementai.',
    ],
    'mimes'                   => ':attribute turi buti failas, kurio tipas:: reikšmes.',
    'mimetypes'               => ':attribute turi buti failas, kurio tipas:: reikšmes.',
    'min' => [
        'numeric'             => ':attribute turi buti bent :min.',
        'file'                => ':attribute turi buti bent :min kilobaitai.',
        'string'              => ':attribute turi buti bent :min simboliu.',
        'array'               => ':attribute turi buti bent :min elementai.',
    ],
    'not_in'                  => 'Pasirinkta :attribute yra neteisinga.',
    'numeric'                 => ':attribute turi buti skaicius.',
    'present'                 => 'Turi buti laukas :attribute.',
    'regex'                   => ':attribute formatas neteisingas.',
    'required'                => 'Butinas laukas :attribute.',
    'required_if'             => 'Laukelis :attribute butinas, kai :other yra :value.',
    'required_unless'         => 'Laukelis :attribute yra butinas, nebent :other yra :values.',
    'required_with'           => 'Laukelis :attribute butinas, kai yra :values.',
    'required_with_all'       => 'Laukelis :attribute butinas, kai yra :values.',
    'required_without'        => 'Laukelis :attribute butinas, kai nera :values.',
    'required_without_all'    => 'Laukelis :attribute butinas, kai nera ne vieno iš :values.',
    'same'                    => ':attribute ir :other turi sutapti.',
    'size' => [
        'numeric'             => ':attribute turi buti :size.',
        'file'                => ':attribute turi buti :size kilobaitai.',
        'string'              => ':attribute turi buti :size simboliai.',
        'array'               => ':attribute turi buti :size elementai.',
    ],
    'string'                  => ':attribute turi buti eilute.',
    'timezone'                => ':attribute turi buti galiojanti zona.',
    'unique'                  => ':attribute jau buvo paimtas.',
    'uploaded'                => 'Nepavyko ikelti :attribute.',
    'url'                     => ':attribute formatas neteisingas.',
    'captcha'                 => ':attribute neteisingas',

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | Here you may specify custom validation messages for attributes using the
    | convention "attribute.rule" to name the lines. This makes it quick to
    | specify a specific custom language line for a given attribute rule.
    |
    */

    'custom'               => [
        'attribute-name' => [
            'rule-name' => 'custom-message',
        ],
        'field_label'    => [
            'regex' => 'Field Label contains an invalid character. Allowed characters: A-Z, a-z, 0-9, space, -, _',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Attributes
    |--------------------------------------------------------------------------
    |
    | The following language lines are used to swap attribute place-holders
    | with something more reader friendly such as E-Mail Address instead
    | of "email". This simply helps us make messages a little cleaner.
    |
    */

    'attributes'           => [],

];

return \FI\Support\TranslationOverride::override(__FILE__, $translations);
