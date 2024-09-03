<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Discover Definitions
    |--------------------------------------------------------------------------
    |
    | If enabled is set to true, your KPI definitions will be automatically discovered when taking snapshot.
    | Customize the path to indicate the directory where your definitions are located in your app.
    | The KPI definitions will be discovered from the path and its subdirectories
    |
    */
    'discover' => [
        'enabled' => true,
        /**
         * This path will be used with `app_path` helper like `app_path('/app/Kpis')`
         */
        'path' => '/app/Kpis',
    ],

    /*
    |--------------------------------------------------------------------------
    | Registered Definitions
    |--------------------------------------------------------------------------
    |
    | You can manually register your kpi definitions if you are not using "auto-discover"
    | or if you want to add more deifnitions not stored in the main path
    |
    */
    'definitions' => [],
];
