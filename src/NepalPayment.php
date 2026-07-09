<?php

namespace Sagartimilsina\NepalPayment;

use Sagartimilsina\NepalPayment\Gateways\EsewaGateway;
use Sagartimilsina\NepalPayment\Gateways\KhaltiGateway;

/**
 * Plain-PHP friendly entry point -- use this directly if you're not in
 * a Laravel app and don't want to touch NepalPaymentManager yourself.
 *
 * Example:
 *
 *   $payment = new NepalPayment([
 *       'esewa' => ['product_code' => 'EPAYTEST', 'secret_key' => '...', ...],
 *       'khalti' => ['secret_key' => '...', 'base_url' => '...'],
 *   ]);
 *
 *   $result = $payment->esewa()->initiate($request);
 */
final class NepalPayment
{
    private readonly NepalPaymentManager $manager;

    /**
     * @param  array<string, mixed>  $config  Same shape as config/nepal-payment.php.
     */
    public function __construct(array $config)
    {
        $this->manager = new NepalPaymentManager($config);
    }

    public function esewa(): EsewaGateway
    {
        return $this->manager->esewa();
    }

    public function khalti(): KhaltiGateway
    {
        return $this->manager->khalti();
    }
}