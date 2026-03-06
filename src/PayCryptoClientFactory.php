<?php

declare(strict_types=1);

namespace PayCrypto\Client;

use cryptoscan\CryptoScanClient;
use cryptoscan\factory\AuthFactory;
use cryptoscan\provider\HttpClientProvider;

final class PayCryptoClientFactory
{
    public static function createFromSignature(
        string $publicKey,
        string $privateKey,
        string $baseUrl = 'https://api.paycrypto.one/api/v1/'
    ): CryptoScanClient {
        $auth = AuthFactory::signature($publicKey, $privateKey);
        $provider = new HttpClientProvider(new PayCryptoHttpClient($baseUrl));
        $provider->setAuthCredentials($auth);
        return new CryptoScanClient($auth, $provider);
    }

    public static function createFromPrivateKey(
        string $publicKey,
        string $privateKey,
        string $baseUrl = 'https://api.paycrypto.one/api/v1/'
    ): CryptoScanClient {
        $auth = AuthFactory::privateKey($publicKey, $privateKey);
        $provider = new HttpClientProvider(new PayCryptoHttpClient($baseUrl));
        $provider->setAuthCredentials($auth);
        return new CryptoScanClient($auth, $provider);
    }

    public static function createFromEnv(string $mode = 'signature'): CryptoScanClient
    {
        $publicKey = Env::get('PAYCRYPTO_PUBLIC_KEY');
        $privateKey = Env::get('PAYCRYPTO_PRIVATE_KEY');
        $baseUrl = Env::get('PAYCRYPTO_BASE_URL', 'https://api.paycrypto.one/api/v1/');
        if ($mode === 'private-key') {
            return self::createFromPrivateKey($publicKey, $privateKey, $baseUrl);
        }
        return self::createFromSignature($publicKey, $privateKey, $baseUrl);
    }
}
