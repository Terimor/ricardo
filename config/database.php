<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Database Connection Name
    |--------------------------------------------------------------------------
    |
    | Here you may specify which of the database connections below you wish
    | to use as your default connection for all database work. Of course
    | you may use many connections at once using the Database library.
    |
    */

    'default' => 'mongodb',

    /*
    |--------------------------------------------------------------------------
    | Database Connections
    |--------------------------------------------------------------------------
    |
    | Here are each of the database connections setup for your application.
    | Of course, examples of configuring each database platform that is
    | supported by Laravel is shown below to make development simple.
    |
    |
    | All database work in Laravel is done through the PHP PDO facilities
    | so make sure you have the driver for your particular database of
    | choice installed on your machine before you begin development.
    |
    */

    'connections' => [
        'mongodb' => [
            'driver'   => 'mongodb',
            'dsn' => env('MONGODB_DSN_ODIN'),
            'database' => 'odin',
            //'database' => explode('/', env('MONGODB_DSN_ODIN'))[3],
            //'database' => array_values(array_slice(explode('/', env('MONGODB_DSN_ODIN')), -1))[0],
            /*'options'  => env('ENVIRONMENT', 'production') === 'production' ? [
                'ssl' => true,
                'replicaSet' => 'odin-prod-shard-0',
                'authSource' => 'admin',
                'retryWrites' => true,
                'w' => 'majority'
            ] : []*/
        ],
    ],

    'redis' => [
        'client' => 'predis',
        'options' => [
            //'cluster' => 'predis',
            'prefix' => '',
        ],
        'cache' => [
            'host' => env('REDIS_HOST'),
            'password' => null,
            'port' => 6379,
            'database' => 0,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Migration Repository Table
    |--------------------------------------------------------------------------
    |
    | This table keeps track of all the migrations that have already run for
    | your application. Using this information, we can determine which of
    | the migrations on disk haven't actually been run in the database.
    |
    */
    'migrations' => 'migration',
];