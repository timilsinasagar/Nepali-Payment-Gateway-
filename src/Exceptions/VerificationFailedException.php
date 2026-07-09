<?php

namespace Sagartimilsina\NepalPayment\Exceptions;

/**
 * Thrown when a gateway's verification call itself fails at the network/
 * HTTP level (timeout, 5xx, malformed response). This is different from
 * a payment simply being unsuccessful -- an unsuccessful-but-verified
 * payment returns a normal PaymentVerificationResult with a FAILED
 * status, it does not throw.
 */
class VerificationFailedException extends PaymentException
{
}
