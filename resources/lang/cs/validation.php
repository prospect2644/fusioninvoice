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

    'accepted'                => 'Je treba prijmout :attribute.',
    'active_url'              => ':attribute není platná adresa URL.',
    'after'                   => ':attribute musí být datum následující po :date.',
    'after_or_equal'          => ':attribute musí být datum po nebo rovno :date.',
    'alpha'                   => ':attribute muže obsahovat pouze písmena.',
    'alpha_dash'              => ':attribute muže obsahovat pouze písmena, císlice a pomlcky.',
    'alpha_num'               => ':attribute muže obsahovat pouze písmena a císla.',
    'array'                   => ':attribute musí být pole.',
    'before'                  => ':attribute musí být datum pred :date.',
    'before_or_equal'         => ':attribute musí být datum pred nebo rovno :date.',
    'between' => [
        'numeric'             => ':attribute musí být mezi :min a :max.',
        'file'                => ':attribute musí být mezi :min a :max kilobajtu.',
        'string'              => ':attribute musí být mezi znaky :min a :max.',
        'array'               => ':attribute musí mít mezi položkami :min a :max.',
    ],
    'boolean'                 => 'Pole :attribute musí být true nebo false.',
    'confirmed'               => 'Potvrzení :attribute se neshoduje.',
    'date'                    => ':attribute není platné datum.',
    'date_format'             => ':attribute neodpovídá formátu :format.',
    'different'               => ':attribute a :other se musí lišit.',
    'digits'                  => ':attribute musí být :digits císlice.',
    'digits_between'          => ':attribute musí být mezi císlicemi :min a :max.',
    'dimensions'              => ':attribute má neplatné rozmery obrázku.',
    'distinct'                => 'Pole :attribute má duplicitní hodnotu.',
    'email'                   => ':attribute musí být platná e-mailová adresa.',
    'exists'                  => 'Vybraná :attribute je neplatná.',
    'file'                    => ':attribute musí být soubor.',
    'filled'                  => 'Pole :attribute musí mít hodnotu.',
    'image'                   => ':attribute musí být obrázek.',
    'in'                      => 'Vybraná :attribute je neplatná.',
    'in_array'                => 'Pole :attribute v :other. neexistuje',
    'integer'                 => ':attribute musí být celé císlo.',
    'ip'                      => ':attribute musí být platná adresa IP.',
    'ipv4'                    => ':attribute musí být platná adresa IPv4.',
    'ipv6'                    => ':attribute musí být platná adresa IPv6.',
    'json'                    => ':attribute musí být platný retezec JSON.',
    'max' => [
        'numeric'             => ':attribute nesmí být vetší než :max.',
        'file'                => ':attribute nesmí být vetší než :max kilobajtu.',
        'string'              => ':attribute nesmí být vetší než :max znaku.',
        'array'               => ':attribute nemusí mít více než :max položek.',
    ],
    'mimes'                   => ':attribute musí být soubor typu:: values.',
    'mimetypes'               => ':attribute musí být soubor typu:: values.',
    'min' => [
        'numeric'             => ':attribute musí být alespon :min.',
        'file'                => ':attribute musí mít alespon :min kilobajtu.',
        'string'              => ':attribute musí mít alespon :min znaku.',
        'array'               => ':attribute musí mít alespon :min položek.',
    ],
    'not_in'                  => 'Vybraná :attribute je neplatná.',
    'numeric'                 => ':attribute musí být císlo.',
    'present'                 => 'Pole :attribute musí být prítomno.',
    'regex'                   => 'Formát :attribute je neplatný.',
    'required'                => 'Pole :attribute je povinné.',
    'required_if'             => 'Pole :attribute je povinné, když je :other :value.',
    'required_unless'         => 'Pole :attribute je povinné, pokud není :other v :values.',
    'required_with'           => 'Pole :attribute je povinné, pokud je k dispozici :values.',
    'required_with_all'       => 'Pole :attribute je povinné, pokud je k dispozici :values.',
    'required_without'        => 'Pole :attribute je povinné, pokud není k dispozici :values.',
    'required_without_all'    => 'Pole :attribute je povinné, pokud není k dispozici žádný z :values.',
    'same'                    => ':attribute a :other se musí shodovat.',
    'size' => [
        'numeric'             => ':attribute musí být :size.',
        'file'                => ':attribute musí být :size kilobajtu.',
        'string'              => ':attribute musí mít :size znaku.',
        'array'               => ':attribute musí obsahovat položky :size.',
    ],
    'string'                  => ':attribute musí být retezec.',
    'timezone'                => ':attribute musí být platná zóna.',
    'unique'                  => ':attribute již byla porízena.',
    'uploaded'                => 'Nahrávání :attribute se nezdarilo.',
    'url'                     => 'Formát :attribute je neplatný.',
    'captcha'                 => ':attribute není správný',

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
