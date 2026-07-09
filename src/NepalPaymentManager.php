<?php

namespace Sagartimilsina\NepalPayment;

use Sagartimilsina\NepalPayment\Contracts\PaymentGatewayInterface;
use Sagartimilsina\NepalPayment\Enums\Gateway;
use Sagartimilsina\NepalPayment\Exceptions\InvalidConfigurationException;
use Sagartimilsina\NepalPayment\Gateways\EsewaGateway;
use Sagartimilsina\NepalPayment\Gateways\KhaltiGateway;

/**
 * Builds gateway instances from a plain config array and hands them out
 * by name. Works identically whether the config array came from
 * Laravel's config() helper or was built by hand in plain PHP.
 */
final class NepalPaymentManager
{
    /** @var array<string, PaymentGatewayInterface> */
    private array $resolved = [];

    /**
     * @param  array<string, mixed>  $config  Same shape as config/nepal-payment.php.
     */
    public function __construct(private readonly array $config)
    {
    }

    public function esewa(): EsewaGateway
    {
        return $this->resolved[Gateway::ESEWA->value] ??= new EsewaGateway(
            productCode: $this->config['esewa']['product_code'] ?? '',
            secretKey: $this->config['esewa']['secret_key'] ?? '',
            formAction: $this->config['esewa']['form_action'] ?? '',
            statusCheckUrl: $this->config['esewa']['status_check_url'] ?? '',
        );
    }

    public function khalti(): KhaltiGateway
    {
        return $this->resolved[Gateway::KHALTI->value] ??= new KhaltiGateway(
            secretKey: $this->config['khalti']['secret_key'] ?? '',
            baseUrl: $this->config['khalti']['base_url'] ?? '',
        );
    }

    public function gateway(Gateway $gateway): PaymentGatewayInterface
    {
        return match ($gateway) {
            Gateway::ESEWA => $this->esewa(),
            Gateway::KHALTI => $this->khalti(),
        };
    }

    /**
     * Convenience for when you have the gateway name as a string, e.g.
     * from a form field or route parameter.
     */
    public function driver(string $name): PaymentGatewayInterface
    {
        $gateway = Gateway::tryFrom($name);

        if ($gateway === null) {
            throw new InvalidConfigurationException(
                "Unknown payment gateway \"{$name}\". Expected one of: ".
                implode(', ', array_column(Gateway::cases(), 'value'))
            );
        }

        return $this->gateway($gateway);
    }
}