# Nepal Payment (eSewa + Khalti) for PHP & Laravel

[![License](https://img.shields.io/badge/license-MIT-blue.svg)](LICENSE)
[![PHP Version](https://img.shields.io/badge/php-%5E8.2-777bb4.svg)](composer.json)

One package to accept payments through **eSewa** and **Khalti** — Nepal's two most used payment apps — from PHP or Laravel.

Both gateways work through the same simple methods, so switching between them (or supporting both) doesn't mean writing separate code for each.

---

## What this package does

- Starts a payment with **eSewa** (builds and signs the payment form for you)
- Starts a payment with **Khalti** (calls their API and gives you a link to redirect to)
- Verifies a payment directly with the gateway's server afterward — never trusts what comes back in the URL alone
- Works in plain PHP, and auto-sets-itself-up in Laravel (10, 11, 12, 13)
- Comes with a working demo page you can turn on to try it out
- Ships with tests so the tricky part — the signature math — is checked automatically

---

## What you need before installing

- PHP 8.2 or newer
- The `curl` and `json` PHP extensions (almost every PHP install already has these)
- Laravel 10–13 (optional — works without Laravel too)

---

## How to install it

```bash
composer require sagartimilsina/nepal-payment
```

If you're using Laravel, it's ready right away. Publish the config file if you want to customize anything:

```bash
php artisan vendor:publish --tag=nepal-payment-config
```

---

## Setting your credentials

Add these to your `.env` file:

```env
NEPAL_PAYMENT_SANDBOX=true

# eSewa — you can leave these two blank while testing.
# eSewa's official public test credentials are used automatically in sandbox mode.
ESEWA_PRODUCT_CODE=
ESEWA_SECRET_KEY=

# Khalti — you MUST get a test secret key from the Khalti merchant dashboard,
# even for sandbox testing. There is no shared public test key for Khalti.
KHALTI_SECRET_KEY=your-khalti-test-secret-key
```

When you're ready to go live, set `NEPAL_PAYMENT_SANDBOX=false` and put your real credentials in.

---

## How to use it (Laravel)

**Step 1 — Start a payment:**

```php
use Sagartimilsina\NepalPayment\Facades\NepalPayment;
use Sagartimilsina\NepalPayment\DataTransferObjects\PaymentInitiationRequest;

$request = new PaymentInitiationRequest(
    amount: 1000,                          // price of the item, in NPR
    orderId: (string) Str::uuid(),         // your own unique order reference
    orderName: 'Order #4821',
    successUrl: route('payment.success'),
    failureUrl: route('payment.failure'),
);

$result = NepalPayment::esewa()->initiate($request);
// or: NepalPayment::khalti()->initiate($request);
```

**Step 2 — Send the customer to the gateway:**

```php
if ($result->requiresFormPost()) {
    // eSewa: show a form that auto-submits itself to eSewa
    return view('your-esewa-redirect-view', [
        'formAction' => $result->formAction,
        'formFields' => $result->formFields,
    ]);
}

// Khalti: just redirect, no form needed
return redirect()->away($result->redirectUrl);
```

**Step 3 — Verify the payment when the customer comes back:**

```php
// eSewa needs to be told the amount again, since it can't look it up on its own
$verification = NepalPayment::esewa()->verify($transactionUuid, ['amount' => 1000]);

// Khalti just needs its own reference (called "pidx")
$verification = NepalPayment::khalti()->verify($pidx);

if ($verification->isSuccessful()) {
    // mark the order as paid
}
```

**Always verify on your server before marking anything as paid.** Never trust the redirect URL by itself — someone could edit it in their browser.

---

## Using it without Laravel

```php
require 'vendor/autoload.php';

use Sagartimilsina\NepalPayment\NepalPayment;
use Sagartimilsina\NepalPayment\DataTransferObjects\PaymentInitiationRequest;

$payment = new NepalPayment([
    'esewa' => [
        'product_code' => 'EPAYTEST',
        'secret_key' => '8gBm/:&EnhH.1/q',
        'form_action' => 'https://rc-epay.esewa.com.np/api/epay/main/v2/form',
        'status_check_url' => 'https://rc.esewa.com.np/api/epay/transaction/status/',
    ],
    'khalti' => [
        'secret_key' => 'your-khalti-test-secret-key',
        'base_url' => 'https://dev.khalti.com/api/v2',
    ],
]);

$result = $payment->esewa()->initiate($request);
```

---

## Try it out with the built-in demo page

Turn it on in `.env`:

```env
NEPAL_PAYMENT_DEMO_ROUTE=true
```

Then visit `/nepal-payment-demo`. Pick a gateway, type an amount, and go through a real sandbox payment.

⚠️ **Turn this off before going live.** It's for local testing only.

---

## A few important things to know

- **eSewa amounts** are plain numbers like `1000.00`. **Khalti amounts** must be sent in *paisa* (so 1000 rupees = 100000). This package converts that for you automatically — you always work in plain rupees.
- **eSewa doesn't hand you a link** — it needs a signed form that the browser submits directly to eSewa. This package builds that form for you; you just need to render it (see the demo view for a working example).
- **Khalti gives you a direct link** — you just redirect the browser there, nothing else to build.
- **Always double-check the amount.** After verifying, compare the amount the gateway confirms against what you expected — if they don't match, don't mark the order paid.

---

## Error Handling

```php
try {
    NepalPayment::esewa()->initiate($request);
} catch (\Sagartimilsina\NepalPayment\Exceptions\InvalidConfigurationException $e) {
    // missing credentials
} catch (\Sagartimilsina\NepalPayment\Exceptions\PaymentException $e) {
    // anything else went wrong talking to the gateway
}
```

---

## Running the tests

```bash
composer install
composer test
```

The tests check the signature math directly, so if a future code change accidentally breaks how signatures are built, the tests catch it — not a customer failing to pay.

---

## Code style check

```bash
composer lint     # just checks
composer format   # auto-fixes formatting
```

---

## Want to understand how it works internally?

See [ARCHITECTURE.md](ARCHITECTURE.md) for a full explanation of how a payment flows through the package and why it's built this way.

---

## Security

See [SECURITY.md](SECURITY.md) for how to report a security issue, and for the do's and don'ts of using this package safely in production.

---

## Roadmap

- [x] eSewa v2 (ePay) integration
- [x] Khalti ePayment API v2 integration
- [x] Plain PHP + Laravel support
- [x] Bundled demo
- [ ] Fonepay support
- [ ] ConnectIPS support
- [ ] Blade component for a one-line "Pay with eSewa/Khalti" button
- [ ] Webhook/IPN handling helpers

---

## License

MIT — see [LICENSE](LICENSE).

---

## Author

**Sagar Timilsina** — Laravel & Full Stack Developer

- GitHub: [https://github.com/timilsinasagar](https://github.com/timilsinasagar)

If this package helps you, a ⭐ on GitHub is appreciated.