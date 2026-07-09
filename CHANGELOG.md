# Changelog

All notable changes to this package are documented here.
This project follows [Semantic Versioning](https://semver.org/).

## [1.0.0] - Unreleased

### Added
- Initial release.
- `EsewaGateway`: eSewa ePay v2 payment initiation (signed form) and
  status verification, with HMAC-SHA256 signing via `EsewaSigner`.
- `KhaltiGateway`: Khalti ePayment API v2 initiation and lookup
  verification.
- Unified `PaymentGatewayInterface` so both gateways share one
  `initiate()` / `verify()` contract.
- `PaymentInitiationRequest`, `PaymentInitiationResult`,
  `PaymentVerificationResult` DTOs, and a gateway-agnostic
  `PaymentStatus` enum.
- Laravel auto-discovery: service provider, `NepalPayment` facade,
  publishable config.
- Plain-PHP entry point (`NepalPayment` class) — no Laravel required.
- Bundled, opt-in Bootstrap 5 demo (Form Request + thin controller +
  views), disabled by default.
- PHPUnit test suite covering signature generation, status mapping,
  and signed form construction.