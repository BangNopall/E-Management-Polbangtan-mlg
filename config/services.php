<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
        'scheme' => 'https',
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Konseling (E-Klinik SSO Handoff)
    |--------------------------------------------------------------------------
    |
    | E-Management berperan sebagai Identity Provider untuk modul konseling
    | yang tetap dijalankan di E-Klinik (lihat docs/adr/ADR-004). "url" adalah
    | base URL E-Klinik, dan "secret" adalah kunci HMAC-SHA256 bersama untuk
    | menandatangani tiket SSO. Keduanya wajib diisi lewat .env, tidak pernah
    | di dalam kode.
    |
    */

    'konseling' => [
        'url' => env('KONSELING_URL'),
        'secret' => env('KONSELING_SSO_SECRET'),
    ],

];
