# Security Policy

This package handles payment integration. If you find a security issue —
especially anything related to signature verification, secret key
handling, or amount tampering — please do **not** open a public GitHub
issue.

Instead, email **timilsinasagar04@gmail.com** with details. You'll get
a response as soon as possible, and a fix will be prioritized over
regular feature work.

## Guidelines for using this package securely

- **Never** commit real `ESEWA_SECRET_KEY` or `KHALTI_SECRET_KEY`
  values — keep them in `.env`, which should already be in
  `.gitignore` in your Laravel app.
- **Always** call `verify()` server-side after a redirect back from
  either gateway. Never mark an order as paid based solely on the
  success-redirect URL or its query parameters — those can be edited
  by the user in their browser.
- **Always** compare the verified amount returned by `verify()`
  against the amount your own system expected for that order, before
  marking it paid.
- Keep `demo_route_enabled` off in production.
