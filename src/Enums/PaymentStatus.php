<?php

namespace Sagartimilsina\NepalPayment\Enums;

/**
 * A gateway-agnostic payment status.
 *
 * eSewa and Khalti each return their own status strings (e.g. eSewa:
 * "COMPLETE"/"PENDING"/"CANCELED", Khalti: "Completed"/"Pending"/"Expired").
 * Each Gateway class maps the raw provider status into one of these so
 * your application code never has to know which gateway was used.
 */
enum PaymentStatus: string
{
    case SUCCESS = 'success';
    case PENDING = 'pending';
    case FAILED = 'failed';
    case CANCELED = 'canceled';
    case REFUNDED = 'refunded';
}
