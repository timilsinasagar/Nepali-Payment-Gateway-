<?php

namespace Sagartimilsina\NepalPayment\Exceptions;

use RuntimeException;

/**
 * Base exception for anything that goes wrong talking to a payment
 * gateway. Catch this if you want to handle both eSewa and Khalti
 * failures the same way.
 */
class PaymentException extends RuntimeException
{
}
