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

    'accepted'             => ':attribute deve essere accettato.',
    'active_url'           => 'L\'URL :attribute non è un URL valido.',
    'after'                => ':attribute deve essere una data successiva a :date.',
    'alpha'                => ':attribute può contenere solamente lettere.',
    'alpha_dash'           => ':attribute può contenere solamente lettere, numeri e trattini.',
    'alpha_num'            => ':attribute può contenere solo lettere e numeri.',
    'array'                => ':attribute deve essere un array.',
    'before'               => ':attribute deve essere una data precedente a :date.',
    'between'              => [
        'numeric' => ':attribute deve essere compreso tra :min e :max.',
        'file'    => ':attribute deve pesare tra i :min e i :max kilobyte.',
        'string'  => ':attribute deve essere compreso tra :min e :max caratteri.',
        'array'   => ':attribute deve avere tra :min e :max elementi.',
    ],
    'boolean'              => ':attribute deve essere vero o falso.',
    'confirmed'            => 'La conferma per :attribute non combacia.',
    'date'                 => ':attribute non è una data valida.',
    'date_format'          => ':attribute non rispetta il formato :format.',
    'different'            => ':attribute e :other devono essere diversi.',
    'digits'               => ':attribute deve contenere :digits cifre.',
    'digits_between'       => ':attribute deve contenere tra le :min e le :max cifre.',
    'email'                => ':attribute deve essere un indirizzo email valido.',
    'filled'               => 'Il campo :attribute è obbligatorio.',
    'exists'               => 'Il campo :attribute selezionato non è valido.',
    'image'                => ':attribute deve essere un\'immagine.',
    'in'                   => 'Il campo :attribute selezionato non è valido.',
    'integer'              => ':attribute deve essere un numero intero.',
    'ip'                   => ':attribute deve essere un indirizzo IP valido.',
    'max'                  => [
        'numeric' => ':attribute non può essere maggiore di :max.',
        'file'    => ':attribute non può pesare più di :max kilobyte.',
        'string'  => ':attribute non può contenere più di :max caratteri.',
        'array'   => ':attribute non può avere più di :max elementi.',
    ],
    'mimes'                => ':attribute deve essere un file di tipo: :values.',
    'min'                  => [
        'numeric' => ':attribute deve valere almeno :min.',
        'file'    => ':attribute deve pesare almeno :min kilobyte.',
        'string'  => ':attribute deve contenere almeno :min caratteri.',
        'array'   => ':attribute deve contenere almeno :min elementi.',
    ],
    'not_in'               => 'Il campo :attribute selezionato non è valido.',
    'numeric'              => ':attribute deve essere un numero.',
    'regex'                => 'Il formato di :attribute non è valido.',
    'required'             => 'Il campo :attribute è obbligatorio.',
    'required_if'          => 'Il campo :attribute è obbligatorio quando :other vale :value.',
    'required_with'        => 'Il campo :attribute è obbligatorio quando :values è presente.',
    'required_with_all'    => 'Il campo :attribute è obbligatorio quando :values è presente.',
    'required_without'     => 'Il campo :attribute è obbligatorio quando :values non è presente.',
    'required_without_all' => 'Il campo :attribute è obbligatorio quando nessun :values è presente.',
    'same'                 => ':attribute e :other devono combaciare.',
    'size'                 => [
        'numeric' => ':attribute deve valere :size.',
        'file'    => ':attribute deve pesare :size kilobyte.',
        'string'  => ':attribute deve contenere :size caratteri.',
        'array'   => ':attribute deve contenere :size elementi.',
    ],
    'string'               => ':attribute deve essere una stringa.',
    'timezone'             => ':attribute deve essere un fuso orario valido.',
    'unique'               => ':attribute è già in uso.',
    'url'                  => 'Il formato di :attribute non è valido.',

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

    'custom' => [
        'attribute-name' => [
            'rule-name' => 'custom-message',
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

    'attributes' => [],

];

return \FI\Support\TranslationOverride::override(__FILE__, $translations);
