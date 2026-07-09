<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Sandbox mode
    |--------------------------------------------------------------------------
    |
    | When true, both gateways point at their test/sandbox endpoints and
    | the default test credentials below are used unless overridden.
    | Flip this to false (via NEPAL_PAYMENT_SANDBOX=false in .env) only
    | when you have real, live merchant credentials configured below.
    |
    */

    'sandbox' => env('NEPAL_PAYMENT_SANDBOX', true),

    /*
    |--------------------------------------------------------------------------
    | eSewa (ePay v2)
    |--------------------------------------------------------------------------
    |
    | product_code is eSewa's term for your merchant code. The sandbox
    | defaults below ("EPAYTEST" / the well-known test secret) are
    | eSewa's own published test credentials -- safe to commit, and only
    | used when sandbox mode is on and you haven't set your own.
    |
    */

    'esewa' => [
        'product_code' => env('ESEWA_PRODUCT_CODE', 'EPAYTEST'),
        'secret_key' => env('ESEWA_SECRET_KEY', '8gBm/:&EnhH.1/q'),

        'form_action' => env(
            'NEPAL_PAYMENT_SANDBOX', true
        ) ? 'https://rc-epay.esewa.com.np/api/epay/main/v2/form'
            : 'https://epay.esewa.com.np/api/epay/main/v2/form',

        // Note: eSewa's docs are not fully consistent across pages about
        // the production status-check host (some show esewa.com.np,
        // others epay.esewa.com.np) -- double-check against
        // https://developer.esewa.com.np/pages/Epay before going live.
        'status_check_url' => env(
            'NEPAL_PAYMENT_SANDBOX', true
        ) ? 'https://rc.esewa.com.np/api/epay/transaction/status/'
            : 'https://esewa.com.np/api/epay/transaction/status/',
    ],

    /*
    |--------------------------------------------------------------------------
    | Khalti (ePayment API v2)
    |--------------------------------------------------------------------------
    |
    | secret_key is your "Live Secret Key" or "Test Secret Key" from the
    | Khalti merchant dashboard. There is no public shared test key for
    | Khalti (unlike eSewa) -- you must sign up for a merchant account,
    | even for sandbox testing.
    |
    */

    'khalti' => [
        'secret_key' => env('KHALTI_SECRET_KEY', ''),

        'base_url' => env(
            'NEPAL_PAYMENT_SANDBOX', true
        ) ? 'https://dev.khalti.com/api/v2'
            : 'https://khalti.com/api/v2',
    ],

    /*
    |--------------------------------------------------------------------------
    | Bundled demo
    |--------------------------------------------------------------------------
    |
    | Disabled by default, same as the nepali-date package -- this
    | package never adds routes to your app without explicit opt-in.
    | Never leave this enabled in production.
    |
    */

    'demo_route_enabled' => env('NEPAL_PAYMENT_DEMO_ROUTE', false),
    'demo_route_path' => env('NEPAL_PAYMENT_DEMO_ROUTE_PATH', '/nepal-payment-demo'),

];