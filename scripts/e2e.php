<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use cryptoscan\command\InvoiceConfirm;
use cryptoscan\command\InvoiceCreate;
use cryptoscan\command\WidgetCreate;
use cryptoscan\contract\FailureInterface;
use cryptoscan\exception\AuthFailureException;
use cryptoscan\exception\ClientFailureException;
use cryptoscan\exception\InvalidDataException;
use PayCrypto\Client\PayCryptoClientFactory;

final class TestRunner
{
    private int $passed = 0;
    private int $failed = 0;

    public function run(string $name, callable $fn): void
    {
        try {
            $fn();
            $this->passed++;
            echo "PASS {$name}\n";
        } catch (Throwable $e) {
            $this->failed++;
            echo "FAIL {$name}: {$e->getMessage()}\n";
        }
    }

    public function finish(): void
    {
        echo json_encode([
            'passed' => $this->passed,
            'failed' => $this->failed,
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . PHP_EOL;
        if ($this->failed > 0) {
            exit(1);
        }
    }
}

function assertTrue(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

function assertFailureStatus(ClientFailureException $e, int $expectedStatus): void
{
    $response = $e->getResponse();
    if (!$response instanceof FailureInterface) {
        throw new RuntimeException('Failure response is not available');
    }
    assertTrue($response->getStatus() === $expectedStatus, "Expected status {$expectedStatus}, got {$response->getStatus()}");
}

function run(): void
{
    $runner = new TestRunner();
    $client = PayCryptoClientFactory::createFromEnv('signature');
    $clientPrivate = PayCryptoClientFactory::createFromEnv('private-key');
    $invoiceId = 0;
    $orderId = 'paycrypto-e2e-' . time();
    $widgetOrderId = 'paycrypto-widget-' . time();

    $runner->run('invoiceCreate signature', function () use ($client, $orderId, &$invoiceId): void {
        $command = new InvoiceCreate(15.75, $orderId);
        $command->setCurrency('USD')->setCryptocurrency('USDT')->setNetwork('TRC-20')->setMetadata('paycrypto-php-e2e');
        $result = $client->invoiceCreate($command);
        assertTrue($result->isSuccess() === true, 'invoiceCreate isSuccess=false');
        $invoiceId = (int)$result->getId();
        assertTrue($invoiceId > 0, 'invoice id must be > 0');
        assertTrue($result->getFinalAmount() !== '', 'final amount is empty');
        assertTrue($result->getWallet() !== '', 'wallet is empty');
    });

    $runner->run('invoiceDetail', function () use ($client, $orderId, &$invoiceId): void {
        $detail = $client->invoiceDetail($invoiceId);
        assertTrue($detail->isSuccess() === true, 'invoiceDetail isSuccess=false');
        assertTrue((int)$detail->getId() === $invoiceId, 'invoiceDetail id mismatch');
        assertTrue($detail->getClientReferenceId() === $orderId, 'client_reference_id mismatch');
    });

    $runner->run('invoiceSearch', function () use ($client, $orderId): void {
        $list = $client->invoiceSearch($orderId);
        assertTrue($list->isSuccess() === true, 'invoiceSearch isSuccess=false');
        assertTrue(count($list->getItems()) > 0, 'invoiceSearch returned empty list');
    });

    $runner->run('widgetCreate', function () use ($client, $widgetOrderId): void {
        $widget = new WidgetCreate(7.3, $widgetOrderId);
        $widget->setCurrency('USD')->setLang('ru-RU')->setWidgetDescription('paycrypto php client e2e');
        $result = $client->widgetCreate($widget);
        assertTrue($result->isSuccess() === true, 'widgetCreate isSuccess=false');
        assertTrue((int)$result->getId() > 0, 'widget invoice id must be > 0');
        assertTrue($result->getWidgetUrl() !== '', 'widget url is empty');
    });

    $runner->run('invoiceConfirm', function () use ($client, &$invoiceId): void {
        $confirm = new InvoiceConfirm($invoiceId, 'paycrypto-e2e-tx-' . time());
        $result = $client->invoiceConfirm($confirm);
        assertTrue($result->isSuccess() === true, 'invoiceConfirm isSuccess=false');
        assertTrue($result->getStatus() === 'paid_manually', 'invoice status must be paid_manually');
    });

    $runner->run('userDetail', function () use ($client): void {
        $user = $client->userDetail();
        assertTrue($user->isSuccess() === true, 'userDetail isSuccess=false');
        assertTrue($user->getId() !== '', 'user id is empty');
    });

    $runner->run('currencyRate', function () use ($client): void {
        $rates = $client->currencyRate();
        assertTrue($rates->isSuccess() === true, 'currencyRate isSuccess=false');
        assertTrue(count($rates->getItems()) > 0, 'currencyRate list is empty');
    });

    $runner->run('currencyRateStatus', function () use ($client): void {
        $status = $client->currencyRateStatus('USD');
        assertTrue($status->isSuccess() === true, 'currencyRateStatus isSuccess=false');
        assertTrue($status->isSupported() === true, 'USD must be supported');
    });

    $runner->run('private-key auth', function () use ($clientPrivate): void {
        $user = $clientPrivate->userDetail();
        assertTrue($user->isSuccess() === true, 'private-key auth failed');
    });

    $runner->run('negative 400 duplicate client_reference_id', function () use ($client, $orderId): void {
        $command = new InvoiceCreate(1.1, $orderId);
        try {
            $client->invoiceCreate($command);
            throw new RuntimeException('Expected InvalidDataException');
        } catch (InvalidDataException $e) {
            assertFailureStatus($e, 400);
        }
    });

    $runner->run('negative 404 invoiceDetail not found', function () use ($client): void {
        try {
            $client->invoiceDetail(999999999);
            throw new RuntimeException('Expected ClientFailureException');
        } catch (ClientFailureException $e) {
            assertFailureStatus($e, 404);
        }
    });

    $runner->run('negative 401 invalid signature', function (): void {
        putenv('PAYCRYPTO_PRIVATE_KEY=bad_private_key');
        $badClient = PayCryptoClientFactory::createFromEnv('signature');
        try {
            $badClient->userDetail();
            throw new RuntimeException('Expected AuthFailureException');
        } catch (AuthFailureException $e) {
            assertFailureStatus($e, 401);
        }
    });

    $runner->finish();
}

run();
