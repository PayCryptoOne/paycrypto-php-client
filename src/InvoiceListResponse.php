<?php

declare(strict_types=1);

namespace PayCrypto\Client;

final class InvoiceListResponse
{
    private bool $success;
    private array $items;
    private int $total;
    private int $limit;
    private int $offset;

    private function __construct(bool $success, array $items, int $total, int $limit, int $offset)
    {
        $this->success = $success;
        $this->items = $items;
        $this->total = $total;
        $this->limit = $limit;
        $this->offset = $offset;
    }

    public static function fromArray(array $payload): self
    {
        $data = is_array($payload['data'] ?? null) ? $payload['data'] : [];
        $items = array_map(
            static fn (array $item): InvoiceListItem => new InvoiceListItem($item),
            array_values(array_filter($data['items'] ?? [], 'is_array')),
        );
        return new self(
            (bool)($payload['success'] ?? false),
            $items,
            (int)($data['total'] ?? 0),
            (int)($data['limit'] ?? 0),
            (int)($data['offset'] ?? 0),
        );
    }

    public function isSuccess(): bool
    {
        return $this->success;
    }

    public function getItems(): array
    {
        return $this->items;
    }

    public function getTotal(): int
    {
        return $this->total;
    }

    public function getLimit(): int
    {
        return $this->limit;
    }

    public function getOffset(): int
    {
        return $this->offset;
    }
}
