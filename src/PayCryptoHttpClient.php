<?php

declare(strict_types=1);

namespace PayCrypto\Client;

use cryptoscan\provider\HttpClientInterface;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Psr\Http\Message\ResponseInterface;

final class PayCryptoHttpClient extends Client implements HttpClientInterface
{
    public function __construct(string $baseUrl = 'https://api.paycrypto.one/api/v1/')
    {
        parent::__construct(['base_uri' => rtrim($baseUrl, '/') . '/']);
    }

    public function sendRequest($method, $uri, array $headers = [], array $data = []): ResponseInterface
    {
        try {
            return $this->request($method, $uri, [
                'json' => $data,
                'headers' => $headers,
            ]);
        } catch (ClientException $exception) {
            return $exception->getResponse();
        }
    }
}
