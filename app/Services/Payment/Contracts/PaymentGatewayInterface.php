<?php

namespace App\Services\Payment\Contracts;

interface PaymentGatewayInterface
{
    /**
     * Initiate a payment request.
     * Returns redirect URL or form data.
     */
    public function initiatePayment(array $orderData): array;

    /**
     * Verify payment after callback/return.
     */
    public function verifyPayment(array $callbackData): array;

    /**
     * Handle webhook/IPN callback from gateway.
     */
    public function callbackHandler(array $payload): array;

    /**
     * Get gateway name identifier.
     */
    public function getName(): string;

    /**
     * Check if gateway is configured and ready.
     */
    public function isConfigured(): bool;
}
