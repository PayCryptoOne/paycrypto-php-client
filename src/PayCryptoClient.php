<?php

declare(strict_types=1);

namespace PayCrypto\Client;

use cryptoscan\CryptoScanClient;

final class PayCryptoClient
{
    private CryptoScanClient $client;

    public function __construct(
        string $publicKey,
        string $privateKey,
        string $baseUrl = 'https://api.paycrypto.one/api/v1/',
        string $authMode = 'signature'
    ) {
        $this->client = $authMode === 'private-key'
            ? PayCryptoClientFactory::createFromPrivateKey($publicKey, $privateKey, $baseUrl)
            : PayCryptoClientFactory::createFromSignature($publicKey, $privateKey, $baseUrl);
    }

    public function __call(string $name, array $arguments)
    {
        return $this->client->$name(...$arguments);
    }
}
