<?php

namespace App\Services\Payment\Gateways;

/**
 * Stripe Payment Gateway (VISA / Mastercard)
 * Works for Pakistan-registered businesses with Stripe account.
 * Uses Stripe Checkout Sessions for PCI compliance.
 *
 * Supports:
 *  - VISA
 *  - Mastercard
 *  - PKR currency
 *
 * Install SDK: composer require stripe/stripe-php
 */
class StripeGateway extends BaseGateway
{
    public function __construct()
    {
        parent::__construct('card');
    }

    public function getName(): string { return 'card'; }

    public function isConfigured(): bool
    {
        return $this->config !== null
            && $this->config->is_enabled
            && !empty($this->config->api_key);
    }

    private function getStripe(): \Stripe\StripeClient
    {
        return new \Stripe\StripeClient($this->config->api_key);
    }

    private function getPublishableKey(): string
    {
        return $this->config->extra_config['publishable_key'] ?? '';
    }

    /**
     * Create a Stripe Checkout Session
     */
    public function initiatePayment(array $orderData): array
    {
        if (!$this->isConfigured()) {
            return $this->failureResponse('Card payment gateway is not configured.');
        }

        if (!class_exists('\Stripe\StripeClient')) {
            return $this->failureResponse('Stripe PHP SDK not installed. Run: composer require stripe/stripe-php');
        }

        try {
            $stripe = $this->getStripe();

            $lineItems = [];
            foreach ($orderData['items'] as $item) {
                $lineItems[] = [
                    'price_data' => [
                        'currency'     => 'pkr',
                        'unit_amount'  => (int) ($item['unit_price'] * 100), // Paisa
                        'product_data' => [
                            'name'   => $item['product_name'],
                            'images' => isset($item['image']) ? [$item['image']] : [],
                        ],
                    ],
                    'quantity' => $item['quantity'],
                ];
            }

            // Shipping as line item if applicable
            if (($orderData['shipping_cost'] ?? 0) > 0) {
                $lineItems[] = [
                    'price_data' => [
                        'currency'     => 'pkr',
                        'unit_amount'  => (int) ($orderData['shipping_cost'] * 100),
                        'product_data' => ['name' => 'Shipping'],
                    ],
                    'quantity' => 1,
                ];
            }

            $session = $stripe->checkout->sessions->create([
                'payment_method_types' => ['card'],
                'line_items'           => $lineItems,
                'mode'                 => 'payment',
                'success_url'          => route('store.payment.success', ['gateway' => 'card']) . '?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url'           => route('store.checkout.index'),
                'customer_email'       => $orderData['email'],
                'metadata'             => [
                    'order_number' => $orderData['order_number'],
                    'order_id'     => $orderData['order_id'],
                ],
                'payment_intent_data'  => [
                    'metadata' => [
                        'order_number' => $orderData['order_number'],
                    ],
                ],
            ]);

            $this->log('Stripe session created', ['order' => $orderData['order_number'], 'session' => $session->id]);

            return $this->successResponse([
                'redirect_url'    => $session->url,
                'method'          => 'REDIRECT',
                'session_id'      => $session->id,
                'publishable_key' => $this->getPublishableKey(),
                'transaction_ref' => $session->id,
            ]);
        } catch (\Stripe\Exception\ApiErrorException $e) {
            $this->logError('Stripe API error: ' . $e->getMessage());
            return $this->failureResponse('Card payment error: ' . $e->getMessage());
        } catch (\Exception $e) {
            $this->logError('Stripe error: ' . $e->getMessage());
            return $this->failureResponse('Payment initiation failed.');
        }
    }

    /**
     * Verify Stripe payment via session ID
     */
    public function verifyPayment(array $callbackData): array
    {
        try {
            $sessionId = $callbackData['session_id'] ?? '';
            if (empty($sessionId)) {
                return $this->failureResponse('Missing session ID.');
            }

            $stripe  = $this->getStripe();
            $session = $stripe->checkout->sessions->retrieve($sessionId);

            if ($session->payment_status === 'paid') {
                $orderNumber = $session->metadata->order_number ?? '';
                $this->log('Payment verified', ['order' => $orderNumber, 'session' => $sessionId]);
                return $this->successResponse([
                    'order_number'   => $orderNumber,
                    'transaction_id' => $session->payment_intent,
                    'amount'         => $session->amount_total / 100,
                    'message'        => 'Card payment successful',
                ]);
            }

            return $this->failureResponse('Payment not completed. Status: ' . $session->payment_status);
        } catch (\Exception $e) {
            $this->logError('Verification error: ' . $e->getMessage());
            return $this->failureResponse('Payment verification failed.');
        }
    }

    /**
     * Handle Stripe webhook
     */
    public function callbackHandler(array $payload): array
    {
        try {
            $webhookSecret = $this->config->extra_config['webhook_secret'] ?? '';
            $sigHeader     = request()->header('Stripe-Signature');

            $event = \Stripe\Webhook::constructEvent(
                request()->getContent(), $sigHeader, $webhookSecret
            );

            $this->log('Webhook received: ' . $event->type);

            if ($event->type === 'checkout.session.completed') {
                $session = $event->data->object;
                if ($session->payment_status === 'paid') {
                    return $this->successResponse([
                        'order_number'   => $session->metadata->order_number,
                        'transaction_id' => $session->payment_intent,
                        'status'         => 'paid',
                    ]);
                }
            }

            return $this->successResponse(['status' => 'ignored', 'event' => $event->type]);
        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            $this->logError('Webhook signature invalid: ' . $e->getMessage());
            return $this->failureResponse('Invalid webhook signature.');
        }
    }
}
