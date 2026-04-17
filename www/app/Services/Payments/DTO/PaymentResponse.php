<?php

namespace App\Services\Payments\DTO;

class PaymentResponse
{
    public function __construct(
        public bool $success,
        public string $message,
        public ?string $redirectUrl = null,
        public ?string $externalId = null
    ) {}

    public static function make(bool $success, string $message, ?string $redirectUrl = null, ?string $externalId = null): self
    {
        return new self($success, $message, $redirectUrl, $externalId);
    }
}
