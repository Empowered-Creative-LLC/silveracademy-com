<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Coming Soon Banner
    |--------------------------------------------------------------------------
    |
    | When true, the login page shows a "Family Portal Coming Soon" notice.
    | Keep this false while staff are testing the portal.
    |
    */

    'coming_soon' => env('PORTAL_COMING_SOON', false),

    /*
    |--------------------------------------------------------------------------
    | Portal Sandbox
    |--------------------------------------------------------------------------
    |
    | Enables the in-app test lab (/portal/sandbox) and allows
    | `php artisan portal:sandbox` to seed known test accounts.
    | Never enable this in production.
    |
    */

    'sandbox_enabled' => env('PORTAL_SANDBOX', in_array(env('APP_ENV'), ['local', 'sandbox'], true)),

    /*
    | Known password used for every new parent created in the local test lab.
    */
    'sandbox_password' => env('PORTAL_SANDBOX_PASSWORD', 'Sandbox123!'),

];
