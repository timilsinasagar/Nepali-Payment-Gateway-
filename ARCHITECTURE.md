# Architecture

This document walks through how a payment flows through the package,
layer by layer, and why it's structured this way.

## The core idea: one interface, two very different gateways

eSewa and Khalti work in fundamentally different ways at the HTTP level:

- **Khalti** is API-first. You POST order details to their API, they
  hand back a `payment_url`, you redirect the browser there.
- **eSewa v2** has no such API for starting a payment. You build a
  signed HTML form yourself and the *browser* POSTs it directly to
  eSewa's endpoint.

Rather than leaking that difference into your application code, both
gateways implement the same `PaymentGatewayInterface`:

```
initiate(PaymentInitiationRequest): PaymentInitiationResult
verify(string $reference, array $context = []): PaymentVerificationResult
```

`PaymentInitiationResult` is shaped to support both cases at once — it
carries either a `redirectUrl` (Khalti) or a `formAction` +
`formFields` pair (eSewa). Your view checks
`$result->requiresFormPost()` once and renders accordingly. Everything
downstream of that point — verifying, mapping status, showing a
result — is identical regardless of which gateway was used.

## Layers

```
NepalPayment (plain PHP entry point)
NepalPaymentServiceProvider + Facade (Laravel wiring)
        │
        ▼
NepalPaymentManager        — resolves esewa()/khalti()/driver($name) from config
        │
        ▼
PaymentGatewayInterface    — the shared contract
        │
   ┌────┴────┐
   ▼         ▼
EsewaGateway   KhaltiGateway
   │         │
   ▼         ▼
EsewaSigner   HttpClient (cURL, no external HTTP dependency)
```

- **`NepalPaymentManager`** is the only place gateway instances get
  constructed. It reads a plain config array (works identically
  whether that array came from Laravel's `config()` or was built by
  hand), and lazily builds/caches each gateway on first use.

- **`EsewaGateway`** builds the signed form in `initiate()`, and calls
  eSewa's status-check endpoint in `verify()`. The HMAC-SHA256 signing
  logic itself lives in `EsewaSigner`, not the gateway class, because
  the exact same signing scheme is needed in two places (signing the
  outgoing form, and — if you choose to also verify the base64 `data`
  redirect payload client-side — checking an incoming response). Keeping
  it in one class means there's only one place that can get the field
  order wrong.

- **`KhaltiGateway`** talks to Khalti's REST API directly via the
  `HttpClient` support class. Two details worth knowing: Khalti amounts
  are in **paisa** (NPR × 100), and `website_url` is derived
  automatically from your `success_url`'s origin so you don't have to
  pass it separately.

- **`HttpClient`** is a minimal cURL wrapper, not Guzzle. This package
  supports plain PHP (no framework), so pulling in Guzzle as a hard
  dependency would force every consumer to install it even if their
  app already has its own HTTP client. cURL is a PHP core extension,
  so there's nothing extra to install.

- **DTOs are `readonly` and typed**, not associative arrays. This
  means your IDE autocompletes `$result->formattedEn`-style properties
  and a typo like `$result->staus` is a compile-time-visible error
  instead of a silent `null`.

## Why `verify()` takes an optional `$context` array

eSewa's status-check API requires the original `total_amount` as a
query parameter — there's no way to look up "what was this transaction
for" from the reference (`transaction_uuid`) alone. Khalti's lookup API
needs nothing beyond the `pidx`.

Rather than have two different verify method signatures per gateway
(which would break the shared interface), `verify()` takes an optional,
gateway-specific `$context` array. `EsewaGateway` requires
`['amount' => float]` in it and throws `InvalidConfigurationException`
if it's missing; `KhaltiGateway` ignores it entirely. This keeps one
consistent method your application code can call without a type check
on which gateway it's talking to — the difference only shows up in
what you need to have on hand (typically your own order record) before
calling it.

## Security model

1. **Signing, not just formatting.** `EsewaSigner` computes an
   HMAC-SHA256 over an exact, ordered message. Field order is
   commented directly in `EsewaGateway::initiate()` precisely because
   it's easy to "clean up" into alphabetical order by accident and
   silently break every future signature.

2. **Never trust the redirect alone.** Both gateways can, in
   principle, be tricked by a user editing query parameters in their
   browser after a "successful" redirect. `verify()` always makes a
   fresh, direct server-to-server call to the gateway rather than
   trusting anything passed back through the browser. The bundled demo
   controller follows this pattern — it re-verifies via `verify()`
   even though the user is technically already "back" on the success
   page.

3. **Amount is a first-class part of verification, not an afterthought.**
   `PaymentVerificationResult::$amount` is always populated from the
   gateway's own response, not from what you originally asked for —
   so your application code can (and should) compare the two and
   reject a mismatch before marking an order paid.

## What's deliberately left to you

This package does not persist anything — no orders table, no payment
log, no database migration. That's an application concern, not a
gateway-SDK concern, and every app's order model looks different. The
bundled demo uses the session purely as a stand-in for "your orders
table" so it can be tried out with zero setup; a real integration
should replace that session lookup with a real order record.