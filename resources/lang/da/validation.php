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

    'accepted'                => ':attribute skal accepteres.',
    'active_url'              => ':attribute er ikke en gyldig URL.',
    'after'                   => ':attribute skal være en dato efter :date.',
    'after_or_equal'          => ':attribute skal være en dato efter eller lig med :date.',
    'alpha'                   => ':attribute må kun indeholde bogstaver.',
    'alpha_dash'              => ':attribute må kun indeholde bogstaver, tal og bindestreger.',
    'alpha_num'               => ':attribute må kun indeholde bogstaver og tal.',
    'array'                   => ':attribute skal være en matrix.',
    'before'                  => ':attribute skal være en dato før :date.',
    'before_or_equal'         => ':attribute skal være en dato før eller lig med :date.',
    'between' => [
        'numeric'             => ':attribute skal være mellem :min og :max.',
        'file'                => ':attribute skal være mellem :min og :max kilobytes.',
        'string'              => ':attribute skal være mellem :min og :max tegn.',
        'array'               => ':attribute skal have mellem :min og :max.',
    ],
    'boolean'                 => 'Feltet :attribute skal være sandt eller falsk.',
    'confirmed'               => 'Bekræftelsen :attribute stemmer ikke overens.',
    'date'                    => ':attribute er ikke en gyldig dato.',
    'date_format'             => ':attribute matcher ikke formatet :format.',
    'different'               => ':attribute og :other skal være forskellige.',
    'digits'                  => ':attribute skal være :digits cifre.',
    'digits_between'          => ':attribute skal være mellem :min og :max cifre.',
    'dimensions'              => ':attribute har ugyldige billeddimensioner.',
    'distinct'                => 'Feltet :attribute har en duplikatværdi.',
    'email'                   => ':attribute skal være en gyldig e-mail-adresse.',
    'exists'                  => 'Den valgte :attribute er ugyldig.',
    'file'                    => ':attribute skal være en fil.',
    'filled'                  => 'Feltet :attribute skal have en værdi.',
    'image'                   => ':attribute skal være et billede.',
    'in'                      => 'Den valgte :attribute er ugyldig.',
    'in_array'                => 'Feltet :attribute findes ikke i :other.',
    'integer'                 => ':attribute skal være et heltal.',
    'ip'                      => ':attribute skal være en gyldig IP-adresse.',
    'ipv4'                    => ':attribute skal være en gyldig IPv4-adresse.',
    'ipv6'                    => ':attribute skal være en gyldig IPv6-adresse.',
    'json'                    => ':attribute skal være en gyldig JSON-streng.',
    'max' => [
        'numeric'             => ':attribute er muligvis ikke større end :max.',
        'file'                => ':attribute må ikke være større end :max kilobyte.',
        'string'              => ':attribute må ikke være større end :max tegn.',
        'array'               => ':attribute må muligvis ikke have mere end :max elementer.',
    ],
    'mimes'                   => ':attribute skal være en fil af typen:: værdier.',
    'mimetypes'               => ':attribute skal være en fil af typen:: værdier.',
    'min' => [
        'numeric'             => ':attribute skal være mindst :min.',
        'file'                => ':attribute skal være mindst :min kilobyte.',
        'string'              => ':attribute skal være mindst :min tegn.',
        'array'               => ':attribute skal have mindst :min elementer.',
    ],
    'not_in'                  => 'Den valgte :attribute er ugyldig.',
    'numeric'                 => ':attribute skal være et tal.',
    'present'                 => 'Feltet :attribute skal være til stede.',
    'regex'                   => 'Formatet :attribute er ugyldigt.',
    'required'                => 'Feltet :attribute er påkrævet.',
    'required_if'             => 'Feltet :attribute kræves, når :other er :value.',
    'required_unless'         => 'Feltet :attribute er påkrævet, medmindre :other er i :values.',
    'required_with'           => 'Feltet :attribute kræves, når :values er til stede.',
    'required_with_all'       => 'Feltet :attribute kræves, når :values er til stede.',
    'required_without'        => 'Feltet :attribute kræves, når :values ikke er til stede.',
    'required_without_all'    => 'Feltet :attribute kræves, når ingen af :values er til stede.',
    'same'                    => ':attribute og :other skal matche.',
    'size' => [
        'numeric'             => ':attribute skal være :size.',
        'file'                => ':attribute skal være :size kilobytes.',
        'string'              => ':attribute skal være :size tegn.',
        'array'               => ':attribute skal indeholde :size-emner.',
    ],
    'string'                  => ':attribute skal være en streng.',
    'timezone'                => ':attribute skal være en gyldig zone.',
    'unique'                  => ':attribute er allerede taget.',
    'uploaded'                => ':attribute kunne ikke uploades.',
    'url'                     => 'Formatet :attribute er ugyldigt.',
    'captcha'                 => ':attribute er ikke korrekt',

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
