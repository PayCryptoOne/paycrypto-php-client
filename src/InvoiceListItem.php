<?php

declare(strict_types=1);

namespace PayCrypto\Client;

final class InvoiceListItem
{
    public function __construct(private array $data)
    {
    }

    public function toArray(): array
    {
        return $this->data;
    }

    public function getId(): int
    {
        return (int)($this->data['id'] ?? 0);
    }

    public function getMerchantId(): string
    {
        return (string)($this->data['merchant_id'] ?? '');
    }

    public function getMerchantName(): ?string
    {
        $value = $this->data['merchant_name'] ?? null;
        return $value === null ? null : (string)$value;
    }

    public function getCryptocurrency(): string
    {
        return (string)($this->data['cryptocurrency'] ?? '');
    }

    public function getNetwork(): string
    {
        return (string)($this->data['network'] ?? '');
    }

    public function getWallet(): string
    {
        return (string)($this->data['wallet'] ?? '');
    }

    public function getPayerWallet(): ?string
    {
        $value = $this->data['payer_wallet'] ?? null;
        return $value === null ? null : (string)$value;
    }

    public function getTransactionId(): ?string
    {
        $value = $this->data['transaction_id'] ?? null;
        return $value === null ? null : (string)$value;
    }

    public function getTransactionExplorerUrl(): ?string
    {
        $value = $this->data['transaction_explorer_url'] ?? null;
        return $value === null ? null : (string)$value;
    }

    public function getSourceCurrency(): ?string
    {
        $value = $this->data['source_currency'] ?? null;
        return $value === null ? null : (string)$value;
    }

    public function getSourceAmount(): ?string
    {
        $value = $this->data['source_amount'] ?? null;
        return $value === null ? null : (string)$value;
    }

    public function getPaymentAmount(): ?string
    {
        $value = $this->data['payment_amount'] ?? null;
        return $value === null ? null : (string)$value;
    }

    public function getFinalAmount(): float
    {
        return (float)($this->data['final_amount'] ?? 0);
    }

    public function getRequestedAmount(): string
    {
        return (string)($this->data['requested_amount'] ?? '');
    }

    public function getStatus(): string
    {
        return (string)($this->data['status'] ?? '');
    }

    public function getClientReferenceId(): string
    {
        return (string)($this->data['client_reference_id'] ?? '');
    }

    public function getMetadata(): ?string
    {
        $value = $this->data['metadata'] ?? null;
        return $value === null ? null : (string)$value;
    }

    public function getCreatedAt(): int
    {
        return (int)($this->data['created_at'] ?? 0);
    }

    public function getPaidAt(): ?int
    {
        $value = $this->data['paid_at'] ?? null;
        return $value === null ? null : (int)$value;
    }

    public function getExpireAt(): int
    {
        return (int)($this->data['expire_at'] ?? 0);
    }
}
