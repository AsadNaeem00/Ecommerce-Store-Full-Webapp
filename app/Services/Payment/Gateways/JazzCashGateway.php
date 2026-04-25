<?php

namespace App\Services\Payment\Gateways;

/**
 * JazzCash Payment Gateway
 * API Reference: https://developer.jazzcash.com.pk/
 *
 * Supports:
 *  - Mobile Account (MA) payments
 *  - Card payments (MPAY)
 *  - Voucher (OTC) payments
 *
 * Test Credentials:
 *  Merchant ID:  MC12345
 *  Password:     test_password
 *  Hash Key:     test_hash_key
 */
class JazzCashGateway extends BaseGateway
{
    private const LIVE_MWALLET_URL = 'https://payments.jazzcash.com.pk/ApplicationAPI/API/Payment/DoTransaction';
    private const TEST_MWALLET_URL = 'https://sandbox.jazzcash.com.pk/ApplicationAPI/API/Payment/DoTransaction';
    private const LIVE_PAGE_URL    = 'https://payments.jazzcash.com.pk/CustomerPortal/transactionmanagement/merchantform/';
    private const TEST_PAGE_URL    = 'https://sandbox.jazzcash.com.pk/CustomerPortal/transactionmanagement/merchantform/';
    private const SUCCESS_CODES    = ['000'];

    public function __construct()
    {
        parent::__construct('jazzcash');
    }

    public function getName(): string { return 'jazzcash'; }

    private function getHashKey(): string
    {
        return $this->config->extra_config['hash_key'] ?? '';
    }

    private function getMWalletUrl(): string
    {
        return $this->testMode ? self::TEST_MWALLET_URL : self::LIVE_MWALLET_URL;
    }

    private function getPageUrl(): string
    {
        return $this->testMode ? self::TEST_PAGE_URL : self::LIVE_PAGE_URL;
    }

    /**
     * Generate HMAC-SHA256 hash - JazzCash requires sorted keys
     */
    private function generateHash(array $data): string
    {
        ksort($data);
        $hashStr = $this->getHashKey() . '&' . implode('&', array_values($data));
        return hash_hmac('sha256', $hashStr, $this->getHashKey());
    }

    /**
     * Initiate JazzCash payment
     * Supports two flows: redirect (hosted page) or direct API (MA)
     */
    public function initiatePayment(array $orderData): array
    {
        if (!$this->isConfigured()) {
            return $this->failureResponse('JazzCash gateway is not configured.');
        }

        try {
            $merchantId  = $this->config->merchant_id;
            $password    = $this->config->api_key ?? '';
            $returnUrl   = $this->config->extra_config['return_url']
                           ?? route('store.payment.callback', ['gateway' => 'jazzcash']);

            $txnRefNo    = 'JC-' . strtoupper(substr(md5(uniqid()), 0, 10));
            $txnDateTime = date('YmdHis');
            $expiryDate  = date('YmdHis', strtotime('+1 hour'));

            // Hosted checkout page (works for card + MA)
            $formData = [
                'pp_Version'          => '1.1',
                'pp_TxnType'          => 'MWALLET',
                'pp_Language'         => 'EN',
                'pp_MerchantID'       => $merchantId,
                'pp_SubMerchantID'    => '',
                'pp_Password'         => $password,
                'pp_BankID'           => 'TBANK',
                'pp_ProductID'        => 'RETL',
                'pp_TxnRefNo'         => $txnRefNo,
                'pp_Amount'           => (int) ($orderData['amount'] * 100), // Paisas
                'pp_TxnCurrency'      => 'PKR',
                'pp_TxnDateTime'      => $txnDateTime,
                'pp_BillReference'    => $orderData['order_number'],
                'pp_Description'      => 'Payment for order ' . $orderData['order_number'],
                'pp_TxnExpiryDateTime'=> $expiryDate,
                'pp_ReturnURL'        => $returnUrl,
                'pp_MobileNumber'     => $orderData['phone'] ?? '',
                'pp_CNIC'             => '',
                'pp_Frequency'        => 'SINGLE',
                'pp_NumPayments'      => '0',
                'pp_CustomerID'       => $orderData['email'] ?? '',
                'pp_CustomerEmail'    => $orderData['email'] ?? '',
                'ppmpf_1'             => $orderData['order_number'],
                'ppmpf_2'             => '',
                'ppmpf_3'             => '',
                'ppmpf_4'             => '',
                'ppmpf_5'             => '',
            ];

            // Build hash (exclude pp_SecureHash from hash input)
            $hashData = $formData;
            $formData['pp_SecureHash'] = $this->generateHash($hashData);

            $this->log('Payment initiated', ['order' => $orderData['order_number'], 'txn_ref' => $txnRefNo]);

            return $this->successResponse([
                'redirect_url'    => $this->getPageUrl(),
                'method'          => 'POST',
                'form_data'       => $formData,
                'transaction_ref' => $txnRefNo,
            ]);
        } catch (\Exception $e) {
            $this->logError('Payment initiation failed: ' . $e->getMessage());
            return $this->failureResponse('Payment initiation failed: ' . $e->getMessage());
        }
    }

    /**
     * Verify JazzCash payment response
     */
    public function verifyPayment(array $callbackData): array
    {
        try {
            $receivedHash = $callbackData['pp_SecureHash'] ?? '';

            // Rebuild hash without pp_SecureHash
            $verifyData = collect($callbackData)
                ->except(['pp_SecureHash'])
                ->filter()
                ->toArray();

            $expectedHash = $this->generateHash($verifyData);

            if (!hash_equals($expectedHash, $receivedHash)) {
                $this->logError('Hash mismatch', $callbackData);
                return $this->failureResponse('Hash verification failed.');
            }

            $responseCode = $callbackData['pp_ResponseCode'] ?? '';
            $orderRef     = $callbackData['pp_BillReference'] ?? '';
            $txnRef       = $callbackData['pp_TxnRefNo']      ?? '';
            $amount       = ($callbackData['pp_Amount']        ?? 0) / 100; // Convert from paisas

            if (in_array($responseCode, self::SUCCESS_CODES)) {
                $this->log('Payment verified', ['order' => $orderRef, 'txn' => $txnRef]);
                return $this->successResponse([
                    'order_number'   => $orderRef,
                    'transaction_id' => $txnRef,
                    'amount'         => $amount,
                    'message'        => $callbackData['pp_ResponseMessage'] ?? 'Payment successful',
                ]);
            }

            return $this->failureResponse($callbackData['pp_ResponseMessage'] ?? 'Payment failed', [
                'response_code' => $responseCode,
                'order_number'  => $orderRef,
            ]);
        } catch (\Exception $e) {
            $this->logError('Verification failed: ' . $e->getMessage());
            return $this->failureResponse('Verification error.');
        }
    }

    /**
     * Handle JazzCash IPN callback
     */
    public function callbackHandler(array $payload): array
    {
        $this->log('IPN received', $payload);
        return $this->verifyPayment($payload);
    }
}
