<?php

namespace App\Services\Payment\Gateways;

/**
 * EasyPaisa Payment Gateway
 * API Reference: https://easypaisa.com.pk/online-payment-gateway/
 *
 * Supports:
 *  - OTC (Over the Counter) payments
 *  - MA (Mobile Account) payments
 *  - Card payments via EasyPaisa
 *
 * Test Credentials:
 *  Merchant ID: 12345
 *  API Key:     test_api_key
 *  Hash Key:    test_hash_key
 */
class EasyPaisaGateway extends BaseGateway
{
    private const LIVE_BASE_URL = 'https://easypay.easypaisa.com.pk/tpin/';
    private const TEST_BASE_URL = 'https://easypaystg.easypaisa.com.pk/tpin/';
    private const SUCCESS_CODES = ['0000', '0001'];

    public function __construct()
    {
        parent::__construct('easypaisa');
    }

    public function getName(): string { return 'easypaisa'; }

    private function getBaseUrl(): string
    {
        return $this->testMode ? self::TEST_BASE_URL : self::LIVE_BASE_URL;
    }

    private function getHashKey(): string
    {
        return $this->config->extra_config['hash_key'] ?? '';
    }

    /**
     * Generate HMAC-SHA256 hash for request signing
     */
    private function generateHash(array $data): string
    {
        $hashKey  = $this->getHashKey();
        $hashStr  = implode('&', array_map(
            fn($k, $v) => "{$k}={$v}",
            array_keys($data),
            array_values($data)
        ));
        return strtoupper(hash_hmac('sha256', $hashStr, $hashKey));
    }

    /**
     * Initiate EasyPaisa payment – returns redirect URL and form params
     */
    public function initiatePayment(array $orderData): array
    {
        if (!$this->isConfigured()) {
            return $this->failureResponse('EasyPaisa gateway is not configured.');
        }

        try {
            $storeId      = $this->config->merchant_id;
            $returnUrl    = $this->config->extra_config['return_url']
                            ?? route('store.payment.callback', ['gateway' => 'easypaisa']);

            $transDateTime = date('Ymd His'); // EasyPaisa format
            $transRef      = 'EP-' . $orderData['order_number'] . '-' . time();

            $requestData = [
                'orderId'             => $orderData['order_number'],
                'storeId'             => $storeId,
                'transactionAmount'   => number_format((float) $orderData['amount'], 2, '.', ''),
                'mobileAccountNo'     => $orderData['phone'] ?? '',
                'emailAddress'        => $orderData['email'] ?? '',
                'transactionType'     => 'MA', // MA=Mobile Account, OTC=Over Counter, BANK=Card
                'tokenExpiry'         => date('Ymd His', strtotime('+30 minutes')),
                'bankIdentificationNumber' => '',
                'encryptedHashRequest'=> '',
                'postBackURL'         => $returnUrl,
                'storeIdPassword'     => $this->config->api_key ?? '',
            ];

            // Generate hash
            $hashData = [
                'amount'          => $requestData['transactionAmount'],
                'orderRefNum'     => $requestData['orderId'],
                'paymentMethod'   => $requestData['transactionType'],
                'postBackURL'     => $requestData['postBackURL'],
                'storeId'         => $storeId,
                'timeStamp'       => $transDateTime,
            ];
            $requestData['encryptedHashRequest'] = $this->generateHash($hashData);

            $this->log('Payment initiated', ['order' => $orderData['order_number'], 'amount' => $orderData['amount']]);

            return $this->successResponse([
                'redirect_url' => $this->getBaseUrl() . 'Index',
                'method'       => 'POST',
                'form_data'    => $requestData,
                'transaction_ref' => $transRef,
            ]);
        } catch (\Exception $e) {
            $this->logError('Payment initiation failed: ' . $e->getMessage());
            return $this->failureResponse('Payment initiation failed: ' . $e->getMessage());
        }
    }

    /**
     * Verify EasyPaisa payment response
     */
    public function verifyPayment(array $callbackData): array
    {
        try {
            $responseCode = $callbackData['responseCode'] ?? '';
            $orderId      = $callbackData['orderId']      ?? '';
            $transAmount  = $callbackData['transactionAmount'] ?? '';
            $transRefNo   = $callbackData['transactionReferenceNumber'] ?? '';

            // Validate hash
            $receivedHash = $callbackData['encryptedHashResponse'] ?? '';
            $verifyData = [
                'amount'                => $transAmount,
                'orderRefNum'           => $orderId,
                'paymentMethod'         => $callbackData['paymentMethod'] ?? '',
                'responseCode'          => $responseCode,
                'transactionReferenceNumber' => $transRefNo,
            ];
            $expectedHash = $this->generateHash($verifyData);

            if (!hash_equals($expectedHash, strtoupper($receivedHash))) {
                $this->logError('Hash verification failed', $callbackData);
                return $this->failureResponse('Payment hash verification failed.');
            }

            if (in_array($responseCode, self::SUCCESS_CODES)) {
                $this->log('Payment verified successfully', ['order' => $orderId, 'ref' => $transRefNo]);
                return $this->successResponse([
                    'order_number'  => $orderId,
                    'transaction_id'=> $transRefNo,
                    'amount'        => $transAmount,
                    'message'       => 'Payment successful',
                ]);
            }

            return $this->failureResponse('Payment failed. Response code: ' . $responseCode, [
                'response_code' => $responseCode,
                'order_number'  => $orderId,
            ]);
        } catch (\Exception $e) {
            $this->logError('Payment verification failed: ' . $e->getMessage());
            return $this->failureResponse('Payment verification error.');
        }
    }

    /**
     * Handle IPN (Instant Payment Notification) callback
     */
    public function callbackHandler(array $payload): array
    {
        $this->log('IPN received', $payload);

        $responseCode = $payload['responseCode'] ?? '';
        $orderId      = $payload['orderId']      ?? '';
        $transRef     = $payload['transactionReferenceNumber'] ?? '';

        if (in_array($responseCode, self::SUCCESS_CODES)) {
            return $this->successResponse([
                'order_number'   => $orderId,
                'transaction_id' => $transRef,
                'status'         => 'paid',
                'raw'            => $payload,
            ]);
        }

        return $this->failureResponse('IPN: Payment not successful', [
            'order_number'  => $orderId,
            'response_code' => $responseCode,
        ]);
    }
}
