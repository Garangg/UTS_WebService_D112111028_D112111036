<?php 

return [
    'defaults' => [
        'guard' => 'api',
        'passwords' => 'students',
    ],

    'guards' => [
        'api' => [
            'driver' => 'jwt',
            'provider' => 'students',
        ],
    ],

    'providers' => [
        'students' => [
            'driver' => 'eloquent',
            'model' => App\Models\Student::class,
        ],
    ],
];