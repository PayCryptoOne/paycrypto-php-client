<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use cryptoscan\command\InvoiceConfirm;
use cryptoscan\command\InvoiceCreate;
use cryptoscan\command\WidgetCreate;
use PayCrypto\Client\PayCryptoClientFactory;

function assertTrue(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

function run(): void
{
    $client = PayCryptoClientFactory::createFromEnv('signature');
    $orderId = 'paycrypto-smoke-' . time();
    $create = new InvoiceCreate(5.5, $orderId);
    $create->setCurrency('USD')->setCryptocurrency('USDT')->setNetwork('TRC-20');
    $invoice = $client->invoiceCreate($create);
    assertTrue($invoice->isSuccess() === true, 'invoiceCreate failed');
    $invoiceId = $invoice->getId();

    $detail = $client->invoiceDetail($invoiceId);
    assertTrue($detail->isSuccess() === true, 'invoiceDetail failed');
    assertTrue($detail->getClientReferenceId() === $orderId, 'client_reference_id mismatch');

    $list = $client->invoiceList([
        'client_reference_id' => $orderId,
        'limit' => 20,
        'offset' => 0,
        'sort_by' => 'created_at',
        'sort_order' => 'desc',
    ]);
    assertTrue($list->isSuccess() === true, 'invoiceList failed');
    assertTrue($list->getTotal() >= 1, 'invoiceList total must be >= 1');
    assertTrue(count($list->getItems()) > 0, 'invoiceList empty list');
    assertTrue($list->getItems()[0]->getClientReferenceId() !== '', 'invoiceList item client_reference_id empty');

    $search = $client->invoiceSearch($orderId);
    assertTrue($search->isSuccess() === true, 'invoiceSearch failed');
    assertTrue(count($search->getItems()) > 0, 'invoiceSearch empty list');

    $widget = new WidgetCreate(6.75, 'paycrypto-widget-' . time());
    $widget->setCurrency('USD')->setLang('ru-RU');
    $widgetRes = $client->widgetCreate($widget);
    assertTrue($widgetRes->isSuccess() === true, 'widgetCreate failed');

    $confirm = new InvoiceConfirm($invoiceId, 'paycrypto-smoke-tx-' . time());
    $confirmRes = $client->invoiceConfirm($confirm);
    assertTrue($confirmRes->isSuccess() === true, 'invoiceConfirm failed');

    $user = $client->userDetail();
    assertTrue($user->isSuccess() === true, 'userDetail failed');

    $currencyRate = $client->currencyRate();
    assertTrue($currencyRate->isSuccess() === true, 'currencyRate failed');

    $currencyRateStatus = $client->currencyRateStatus('USD');
    assertTrue($currencyRateStatus->isSuccess() === true, 'currencyRateStatus failed');

    echo json_encode([
        'ok' => true,
        'invoice_id' => $invoiceId,
        'widget_invoice_id' => $widgetRes->getId(),
        'status_after_confirm' => $confirmRes->getStatus(),
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . PHP_EOL;
}

run();
