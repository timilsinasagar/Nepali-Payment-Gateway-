<?php

namespace Sagartimilsina\NepalPayment\Exceptions;

/**
 * Thrown when required config (merchant code, secret key, API key, etc.)
 * is missing or empty. Thrown at initiate()/verify() time, not at boot
 * time, so the package never crashes an app that doesn't use payments.
 */
class InvalidConfigurationException extends PaymentException
{
}
