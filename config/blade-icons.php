<?php
return [

    'sets' => [
        'flux' => [
            'path' => realpath('vendor/flux/icons/resources/svg'),
            'prefix' => 'flux',
        ],

        'heroicons' => [
            'prefix' => 'heroicon-o',
            'paths' => [
                realpath('vendor/blade-ui-kit/blade-heroicons/resources/svg/outline'),
            ],
        ],

        'heroicons-solid' => [
            'prefix' => 'heroicon-s',
            'paths' => [
                realpath('vendor/blade-ui-kit/blade-heroicons/resources/svg/solid'),
            ],
        ],
    ],

];
