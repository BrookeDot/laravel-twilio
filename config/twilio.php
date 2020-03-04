<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Connection Name
    |--------------------------------------------------------------------------
    |
    | Here you may specify which of the Twilio connections below you wish
    | to use as your default connection for your application. You may use
    | many connections at once using this library.
    |
    */

    'default' => env('TWILIO_CONNECTION', 'twilio'),

    /*
    |--------------------------------------------------------------------------
    | Twilio Connections
    |--------------------------------------------------------------------------
    |
    | Here are each of the Twilio connections setup for your application.
    | Each connection is required to provide a "sid", "token", and "from" key,
    | all of which are required parameters for creating a connection to the
    | Twilio REST API.
    |
    | To create a new connection, duplicate the "default" connection
    | configuration as a new entry in your connections array and give
    | it a unique name. You can now access this connection through the
    | connection manager.
    |
    */

    'connections' => [
        'default' => [
            'sid' => env('TWILIO_API_SID', ''),
            'token' => env('TWILIO_API_AUTH_TOKEN', ''),
            'from' => env('TWILIO_API_FROM_NUMBER', ''),
        ],
    ],

];
