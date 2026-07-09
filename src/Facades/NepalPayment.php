<?php

namespace Sagartimilsina\NepalPayment\Facades;

use Illuminate\Support\Facades\Facade;
use Sagartimilsina\NepalPayment\Gateways\EsewaGateway;
use Sagartimilsina\NepalPayment\Gateways\KhaltiGateway;
use Sagartimilsina\NepalPayment\NepalPaymentManager;

/**
 * @method static EsewaGateway esewa()
 * @method static KhaltiGateway khalti()
 * @method static \Sagartimilsina\NepalPayment\Contracts\PaymentGatewayInterface driver(string $name)
 *
 * @see NepalPaymentManager
 */
class NepalPayment extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return NepalPaymentManager::class;
    }
}