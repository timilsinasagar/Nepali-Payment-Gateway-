<?php

namespace Sagartimilsina\NepalPayment\Support;

/**
 * eSewa v2 (ePay) requires an HMAC-SHA256 signature over a specific
 * comma-separated "field=value" message, base64-encoded. Both the
 * outgoing payment form AND the incoming status-check response use this
 * same signing scheme, so it lives in one place to avoid the two ever
 * drifting out of sync.
 *
 * eSewa's own docs specify the message format precisely -- do not
 * reorder the fields, the signature will not match if you do.
 */
final class EsewaSigner
{
    public function __construct(private readonly string $secretKey)
    {
    }

    /**
     * @param  array<string, string>  $fields  Field name => value, in the exact order eSewa expects them signed.
     */
    public function sign(array $fields): string
    {
        $hash = hash_hmac('sha256', $this->buildMessage($fields), $this->secretKey, true);

        return base64_encode($hash);
    }

    /**
     * @param  array<string, string>  $fields
     */
    public function verify(array $fields, string $signature): bool
    {
        return hash_equals($this->sign($fields), $signature);
    }

    /**
     * Builds the "key1=value1,key2=value2" message eSewa expects, in
     * insertion order.
     *
     * @param  array<string, string>  $fields
     */
    private function buildMessage(array $fields): string
    {
        $parts = [];

        foreach ($fields as $key => $value) {
            $parts[] = "{$key}={$value}";
        }

        return implode(',', $parts);
    }
}