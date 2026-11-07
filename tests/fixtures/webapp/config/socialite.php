<?php

return [
    'default'   => 'github',
    'stateless' => true,

    'routes' => [
        'enabled'    => true,
        'prefix'     => 'socialite',
        'middleware' => [],
    ],

    'drivers' => [
        'github' => [
            'client_id'     => 'test-id',
            'client_secret' => 'test-secret',
        ],
    ],
];