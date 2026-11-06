<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Demo Accounts
    |--------------------------------------------------------------------------
    |
    | Credentials used by DemoAccountSeeder to seed one account per role into
    | a deployed environment. They are read through config rather than env()
    | directly, because env() returns null once config:cache has run during
    | the deployment build. Leave a password empty to have the seeder generate
    | a random one and print it.
    |
    */

    'admin_email' => env('DEMO_ADMIN_EMAIL', 'superadmin@kalaluang.test'),
    'admin_password' => env('DEMO_ADMIN_PASSWORD'),

    'company_email' => env('DEMO_COMPANY_EMAIL', 'perusahaan@kalaluang.test'),
    'company_password' => env('DEMO_COMPANY_PASSWORD'),

    'job_seeker_email' => env('DEMO_JOB_SEEKER_EMAIL', 'pelamar@kalaluang.test'),
    'job_seeker_password' => env('DEMO_JOB_SEEKER_PASSWORD'),

];
