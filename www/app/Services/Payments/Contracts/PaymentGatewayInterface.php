<?php

namespace App\Services\Payments\Contracts;

use App\Models\Order;
use App\Services\Payments\DTO\PaymentResponse;

interface PaymentGatewayInterface
{
    /**
     * Translates an internal SaaS Order logic into a valid gateway charge intent.
     * 
     * @param Order $order
     * @param array|null $paymentData Raw transparent sensitive keys locally gathered.
     * @return PaymentResponse
     */
    public function generateCharge(Order $order, ?array $paymentData = null): PaymentResponse;

    /**
     * Reverts a processed transaction directly against the financial institution.
     * 
     * @param Order $order
     * @param float|null $amount Refund partial or full if null.
     * @return bool
     */
    public function refundCharge(Order $order, ?float $amount = null): bool;
}
