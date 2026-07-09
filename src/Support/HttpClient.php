<?php

namespace Sagartimilsina\NepalPayment\Support;

use Sagartimilsina\NepalPayment\Exceptions\VerificationFailedException;

/**
 * A tiny cURL wrapper used for the Khalti API calls and eSewa's status
 * check. Deliberately dependency-free (no Guzzle) so this package works
 * in plain PHP without forcing anything extra into your composer.json.
 */
final class HttpClient
{
    public function __construct(private readonly int $timeoutSeconds = 15)
    {
    }

    /**
     * @param  array<string, string>  $headers
     * @param  array<string, mixed>|null  $jsonBody
     * @return array{status: int, body: array<string, mixed>}
     */
    public function post(string $url, array $jsonBody, array $headers = []): array
    {
        return $this->request('POST', $url, $jsonBody, $headers);
    }

    /**
     * @param  array<string, string>  $headers
     * @return array{status: int, body: array<string, mixed>}
     */
    public function get(string $url, array $headers = []): array
    {
        return $this->request('GET', $url, null, $headers);
    }

    /**
     * @param  array<string, mixed>|null  $jsonBody
     * @param  array<string, string>  $headers
     * @return array{status: int, body: array<string, mixed>}
     */
    private function request(string $method, string $url, ?array $jsonBody, array $headers): array
    {
        $ch = curl_init($url);

        $defaultHeaders = ['Accept: application/json'];

        if ($jsonBody !== null) {
            $defaultHeaders[] = 'Content-Type: application/json';
        }

        foreach ($headers as $key => $value) {
            $defaultHeaders[] = "{$key}: {$value}";
        }

        curl_setopt_array($ch, [
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => $defaultHeaders,
            CURLOPT_TIMEOUT => $this->timeoutSeconds,
            CURLOPT_SSL_VERIFYPEER => true,
        ]);

        if ($jsonBody !== null) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($jsonBody, JSON_THROW_ON_ERROR));
        }

        $responseBody = curl_exec($ch);
        $errorNumber = curl_errno($ch);
        $errorMessage = curl_error($ch);
        $statusCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($errorNumber !== 0) {
            throw new VerificationFailedException(
                "HTTP request to gateway failed: {$errorMessage}"
            );
        }

        $decoded = json_decode((string) $responseBody, true);

        if (! is_array($decoded)) {
            throw new VerificationFailedException(
                "Gateway returned a non-JSON or malformed response (HTTP {$statusCode})."
            );
        }

        return ['status' => $statusCode, 'body' => $decoded];
    }
}