# PayCrypto PHP Client

PHP client for [paycrypto.one](https://paycrypto.one) API.

- **Packagist:** [paycryptoone/paycrypto-php-client](https://packagist.org/packages/paycryptoone/paycrypto-php-client)
- **GitHub:** [PayCryptoOne/paycrypto-php-client](https://github.com/PayCryptoOne/paycrypto-php-client)

## Установка

```bash
composer require paycryptoone/paycrypto-php-client
```

## Ключи: конструктор или переменные окружения

Через конструктор:

```php
<?php

use PayCrypto\Client\PayCryptoClient;

$client = new PayCryptoClient(
    publicKey: 'your_public_key',
    privateKey: 'your_private_key',
    baseUrl: 'https://api.paycrypto.one/api/v1/',
    authMode: 'signature'
);
```

Или через фабрику из env (в `.env` или `getenv`: `PAYCRYPTO_PUBLIC_KEY`, `PAYCRYPTO_PRIVATE_KEY`, `PAYCRYPTO_BASE_URL`):

```php
use PayCrypto\Client\PayCryptoClientFactory;

$client = PayCryptoClientFactory::createFromEnv('signature');
$client = PayCryptoClientFactory::createFromEnv('private-key');
```

## Примеры по эндпоинтам

### Создание инвойса — `invoice` (POST)

```php
use PayCrypto\Client\PayCryptoClient;
use cryptoscan\command\InvoiceCreate;

$client = new PayCryptoClient($publicKey, $privateKey);
$cmd = new InvoiceCreate(10.5, 'order-' . time());
$cmd->setCurrency('USD')->setCryptocurrency('USDT')->setNetwork('TRC-20')->setMetadata('my-order');
$result = $client->invoiceCreate($cmd);
$invoiceId = $result->getId();
$wallet = $result->getWallet();
$finalAmount = $result->getFinalAmount();
```

### Виджет инвойса — `invoice/widget` (POST)

```php
use cryptoscan\command\WidgetCreate;

$widget = new WidgetCreate(7.5, 'widget-order-' . time());
$widget->setCurrency('USD')->setLang('ru-RU')->setWidgetDescription('Оплата заказа');
$result = $client->widgetCreate($widget);
$widgetUrl = $result->getWidgetUrl();
$invoiceId = $result->getId();
```

### Получить инвойс по ID — `invoice/:id` (GET)

```php
$detail = $client->invoiceDetail($invoiceId);
$detail->getId();
$detail->getClientReferenceId();
$detail->getStatus();
$detail->getFinalAmount();
```

### Поиск инвойсов — `invoice?query=` (GET)

```php
$list = $client->invoiceSearch('order-123');
$items = $list->getItems();
```

### Подтверждение оплаты инвойса — `invoice/confirm/:id` (PUT)

```php
use cryptoscan\command\InvoiceConfirm;

$confirm = new InvoiceConfirm($invoiceId, 'tx-hash-or-id-' . time());
$result = $client->invoiceConfirm($confirm);
$status = $result->getStatus();
```

### Текущий пользователь — `user` (GET)

```php
$user = $client->userDetail();
$userId = $user->getId();
```

### Список курсов — `currency-rate` (GET)

```php
$rates = $client->currencyRate();
$items = $rates->getItems();
```

### Статус курса по валюте — `currency-rate/:currency/status` (GET)

```php
$status = $client->currencyRateStatus('USD');
$supported = $status->isSupported();
```

## Проверки

Smoke:

```bash
composer smoke
```

E2E:

```bash
composer e2e
```
