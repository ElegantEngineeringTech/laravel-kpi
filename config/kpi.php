<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Discover Definitions
    |--------------------------------------------------------------------------
    |
    | If 'enabled' is set to true, your KPI definitions will be automatically
    | discovered when taking snapshots.
    | Set the 'path' to specify the directory where your KPI definitions are stored.
    | Definitions will be discovered from this path and its subdirectories.
    |
    */
    'discover' => [
        'enabled' => true,
        /**
         * This path will be used with the `app_path` helper, like `app_path('Kpis')`.
         */
        'path' => 'Kpis',
    ],

    /*
    |--------------------------------------------------------------------------
    | Registered Definitions
    |--------------------------------------------------------------------------
    |
    | You can manually register your KPI definitions if you are not using
    | "discover" or if you want to add additional definitions located elsewhere.
    |
    */
    'definitions' => [],

    /*
    |--------------------------------------------------------------------------
    | Model
    |--------------------------------------------------------------------------
    |
    | Here you can define a custom class to use for your Kpi model
    | (must implement Elegantly\Kpi\Contracts\KpiModelInterface)
    |
    */
    'model' => null,
];
