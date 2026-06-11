<?php

declare(strict_types=1);

namespace PayCrypto\Client;

use cryptoscan\CryptoScanClient;
use RuntimeException;

final class PayCryptoClient
{
    private CryptoScanClient $client;
    private string $publicKey;
    private string $privateKey;
    private string $authMode;
    private PayCryptoHttpClient $httpClient;

    public function __construct(
        string $publicKey,
        string $privateKey,
        string $baseUrl = 'https://api.paycrypto.one/api/v1/',
        string $authMode = 'signature'
    ) {
        $this->publicKey = trim($publicKey);
        $this->privateKey = trim($privateKey);
        $this->authMode = $authMode;
        $this->httpClient = new PayCryptoHttpClient($baseUrl);
        $this->client = $authMode === 'private-key'
            ? PayCryptoClientFactory::createFromPrivateKey($this->publicKey, $this->privateKey, $baseUrl)
            : PayCryptoClientFactory::createFromSignature($this->publicKey, $this->privateKey, $baseUrl);
    }

    public function invoiceList(array $query = []): InvoiceListResponse
    {
        $response = $this->httpClient->sendRequest(
            'GET',
            $this->buildInvoiceListUri($query),
            $this->buildAuthHeaders(),
        );
        $payload = json_decode((string)$response->getBody(), true);
        if (!is_array($payload)) {
            throw new RuntimeException('Invalid JSON response');
        }
        if (($payload['success'] ?? false) !== true) {
            $message = $payload['data']['message'] ?? ('HTTP ' . $response->getStatusCode());
            throw new RuntimeException((string)$message);
        }
        return InvoiceListResponse::fromArray($payload);
    }

    public function __call(string $name, array $arguments)
    {
        return $this->client->$name(...$arguments);
    }

    private function buildInvoiceListUri(array $query): string
    {
        $normalized = [];
        foreach ($query as $key => $value) {
            if ($value === null) {
                continue;
            }
            $normalized[(string)$key] = (string)$value;
        }
        $queryString = http_build_query($normalized);
        return $queryString === '' ? 'invoices' : 'invoices?' . $queryString;
    }

    private function buildAuthHeaders(): array
    {
        $headers = [
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
            'public-key' => $this->publicKey,
        ];
        if ($this->authMode === 'private-key') {
            $headers['private-key'] = $this->privateKey;
            return $headers;
        }
        $headers['signature'] = hash_hmac('sha256', 'api_key=' . $this->publicKey, $this->privateKey);
        return $headers;
    }
}
