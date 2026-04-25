<?php

namespace App\Services\Payment\Gateways;

use App\Models\PaymentConfig;
use App\Services\Payment\Contracts\PaymentGatewayInterface;
use Illuminate\Support\Facades\Log;

abstract class BaseGateway implements PaymentGatewayInterface
{
    protected ?PaymentConfig $config;
    protected bool $testMode;

    public function __construct(string $gateway)
    {
        $this->config   = PaymentConfig::forGateway($gateway);
        $this->testMode = $this->config?->is_test_mode ?? true;
    }

    public function isConfigured(): bool
    {
        return $this->config !== null
            && $this->config->is_enabled
            && !empty($this->config->merchant_id);
    }

    protected function log(string $message, array $context = []): void
    {
        Log::channel('daily')->info("[{$this->getName()}] {$message}", $context);
    }

    protected function logError(string $message, array $context = []): void
    {
        Log::channel('daily')->error("[{$this->getName()}] {$message}", $context);
    }

    protected function successResponse(array $data): array
    {
        return array_merge(['success' => true, 'gateway' => $this->getName()], $data);
    }

    protected function failureResponse(string $message, array $data = []): array
    {
        return array_merge(['success' => false, 'gateway' => $this->getName(), 'message' => $message], $data);
    }
}
