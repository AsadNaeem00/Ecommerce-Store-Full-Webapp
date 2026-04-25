<?php

namespace App\Services\Payment\Gateways;

class CodGateway extends BaseGateway
{
    public function __construct() { parent::__construct('cod'); }
    public function getName(): string { return 'cod'; }
    public function isConfigured(): bool { return $this->config?->is_enabled ?? false; }

    public function initiatePayment(array $orderData): array
    {
        return $this->successResponse([
            'method'          => 'COD',
            'order_number'    => $orderData['order_number'],
            'message'         => 'Cash on Delivery order placed successfully.',
            'redirect_url'    => route('store.order.confirmation', ['order' => $orderData['order_number']]),
        ]);
    }

    public function verifyPayment(array $callbackData): array
    {
        return $this->successResponse(['status' => 'pending_cod']);
    }

    public function callbackHandler(array $payload): array
    {
        return $this->successResponse(['status' => 'cod_not_applicable']);
    }
}
