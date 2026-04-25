<?php

namespace App\Services\Payment;

use App\Services\Payment\Contracts\PaymentGatewayInterface;
use App\Services\Payment\Gateways\{CodGateway, EasyPaisaGateway, JazzCashGateway, StripeGateway};
use InvalidArgumentException;

class PaymentManager
{
    private static array $gateways = [
        'cod'       => CodGateway::class,
        'easypaisa' => EasyPaisaGateway::class,
        'jazzcash'  => JazzCashGateway::class,
        'card'      => StripeGateway::class,
    ];

    public static function gateway(string $name): PaymentGatewayInterface
    {
        if (!isset(self::$gateways[$name])) {
            throw new InvalidArgumentException("Payment gateway [{$name}] is not supported.");
        }
        return new self::$gateways[$name]();
    }

    public static function availableGateways(): array
    {
        $available = [];
        foreach (self::$gateways as $name => $class) {
            $gateway = new $class();
            if ($gateway->isConfigured()) {
                $available[$name] = $gateway;
            }
        }
        return $available;
    }

    public static function gatewayLabels(): array
    {
        return [
            'cod'       => ['label' => 'Cash on Delivery',     'icon' => '💵', 'description' => 'Pay when you receive your order'],
            'easypaisa' => ['label' => 'EasyPaisa',            'icon' => '📱', 'description' => 'Pay via EasyPaisa mobile account'],
            'jazzcash'  => ['label' => 'JazzCash',             'icon' => '📲', 'description' => 'Pay via JazzCash mobile account'],
            'card'      => ['label' => 'Credit / Debit Card',  'icon' => '💳', 'description' => 'VISA, Mastercard – secure online payment'],
        ];
    }
}
