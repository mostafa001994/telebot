<?php

class ZarinPalService
{
    private string $merchantId;

    public function __construct()
    {
        $this->merchantId =
            getenv('ZARINPAL_MERCHANT_ID') ?: '';
    }

    //
    private function tomanToRial(int $amount): int
    {
        return $amount * 10;
    }

    //
    public function request(
        int $amount,
        string $callbackUrl,
        string $description = 'Subscription Payment'
    ) {
        if ($this->merchantId === '') {
            return [
                'ok' => false,
                'error' => 'ZarinPal merchant ID is not configured.',
            ];
        }

        if ($amount <= 0) {
            return [
                'ok' => false,
                'error' => 'Invalid payment amount.',
            ];
        }

        $rialAmount =
            $this->tomanToRial($amount);

        $data = [
            'merchant_id' => $this->merchantId,
            'amount' => $rialAmount,
            'callback_url' => $callbackUrl,
            'description' => $description,
        ];

        $ch = curl_init(
            'https://api.zarinpal.com/pg/v4/payment/request.json'
        );

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,

            CURLOPT_POST => true,

            CURLOPT_POSTFIELDS => json_encode(
                $data,
                JSON_UNESCAPED_UNICODE
            ),

            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Accept: application/json',
            ],

            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_TIMEOUT => 30,
        ]);

        $response = curl_exec($ch);

        if ($response === false) {

            $error = curl_error($ch);

            curl_close($ch);

            return [
                'ok' => false,
                'error' => $error,
            ];
        }

        $httpCode =
            curl_getinfo(
                $ch,
                CURLINFO_HTTP_CODE
            );

        curl_close($ch);

        $result =
            json_decode(
                $response,
                true
            );

        if (!is_array($result)) {

            return [
                'ok' => false,
                'error' =>
                    'Invalid JSON response from ZarinPal',

                'http_code' =>
                    $httpCode,

                'raw_response' =>
                    $response,
            ];
        }

        $result['_http_code'] =
            $httpCode;

        return $result;
    }

    // Verify
    public function verify(
        int $amount,
        string $authority
    ) {
        if ($this->merchantId === '') {

            return [
                'ok' => false,
                'error' =>
                    'ZarinPal merchant ID is not configured.',
            ];
        }

        if ($amount <= 0) {

            return [
                'ok' => false,
                'error' =>
                    'Invalid payment amount.',
            ];
        }

        if ($authority === '') {

            return [
                'ok' => false,
                'error' =>
                    'Invalid authority.',
            ];
        }

        $rialAmount =
            $this->tomanToRial($amount);

        $data = [
            'merchant_id' => $this->merchantId,
            'amount' => $rialAmount,
            'authority' => $authority,
        ];

        $ch = curl_init(
            'https://api.zarinpal.com/pg/v4/payment/verify.json'
        );

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,

            CURLOPT_POST => true,

            CURLOPT_POSTFIELDS => json_encode(
                $data,
                JSON_UNESCAPED_UNICODE
            ),

            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Accept: application/json',
            ],

            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_TIMEOUT => 30,
        ]);

        $response = curl_exec($ch);

        if ($response === false) {

            $error = curl_error($ch);

            curl_close($ch);

            return [
                'ok' => false,
                'error' => $error,
            ];
        }

        $httpCode =
            curl_getinfo(
                $ch,
                CURLINFO_HTTP_CODE
            );

        curl_close($ch);

        $result =
            json_decode(
                $response,
                true
            );

        if (!is_array($result)) {

            return [
                'ok' => false,
                'error' =>
                    'Invalid JSON response from ZarinPal',

                'http_code' =>
                    $httpCode,

                'raw_response' =>
                    $response,
            ];
        }

        $result['_http_code'] =
            $httpCode;

        return $result;
    }
}