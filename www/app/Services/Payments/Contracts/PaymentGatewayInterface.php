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
     * @return PaymentResponse
     */
    public function generateCharge(Order $order): PaymentResponse;
}
