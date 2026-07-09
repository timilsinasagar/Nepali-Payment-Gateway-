<?php
// routes/web.php
//
// Loaded by NepalPaymentServiceProvider only when
// config('nepal-payment.demo_route_enabled') is true, and always within
// the 'web' middleware group (session, CSRF, error bag). Disabled by
// default so the package never adds routes to a production app without
// an explicit opt-in.

use Illuminate\Support\Facades\Route;
use Sagartimilsina\NepalPayment\Http\Controllers\PaymentDemoController;

$path = config('nepal-payment.demo_route_path', '/nepal-payment-demo');

Route::get($path, [PaymentDemoController::class, 'index'])
    ->name('nepal-payment.demo.index');

Route::post("{$path}/pay", [PaymentDemoController::class, 'pay'])
    ->name('nepal-payment.demo.pay');

Route::get("{$path}/callback/{gateway}", [PaymentDemoController::class, 'callback'])
    ->name('nepal-payment.demo.callback');