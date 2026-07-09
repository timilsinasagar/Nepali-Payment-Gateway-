<?php

namespace Sagartimilsina\NepalPayment\Exceptions;

/**
 * Thrown by EsewaGateway when a response's signature does not match the
 * signature we compute ourselves -- a strong sign the response was
 * tampered with, or the secret key is wrong. Treat this as a security
 * event, not a normal failure: log it and do not mark the order paid.
 */
class InvalidSignatureException extends PaymentException
{
}
