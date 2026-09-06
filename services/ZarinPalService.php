<?php

class ZarinPalService
{
    private string $merchantId;

    public function __construct()
    {
        $this->merchantId = getenv('ZARINPAL_MERCHANT_ID') ?: '';
    }

    public function request(int $amount, string $callbackUrl)
    {
        $data = [
            "merchant_id" => $this->merchantId,
            "amount" => $amount,
            "callback_url" => $callbackUrl,
            "description" => "Subscription Payment",
        ];

        $ch = curl_init(
            "https://api.zarinpal.com/pg/v4/payment/request.json"
        );

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode(
                $data,
                JSON_UNESCAPED_UNICODE
            ),
            CURLOPT_HTTPHEADER => [
                "Content-Type: application/json"
            ],
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

        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        curl_close($ch);

        $result = json_decode($response, true);

        if (!is_array($result)) {
            return [
                'ok' => false,
                'error' => 'Invalid JSON response from ZarinPal',
                'http_code' => $httpCode,
                'raw_response' => $response,
            ];
        }

        $result['_http_code'] = $httpCode;

        return $result;
    }

    public function verify(int $amount, string $authority)
    {
        $data = [
            "merchant_id" => $this->merchantId,
            "amount" => $amount,
            "authority" => $authority,
        ];

        $ch = curl_init(
            "https://api.zarinpal.com/pg/v4/payment/verify.json"
        );

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => [
                "Content-Type: application/json"
            ],
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

        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        curl_close($ch);

        $result = json_decode($response, true);

        if (!is_array($result)) {
            return [
                'ok' => false,
                'error' => 'Invalid JSON response from ZarinPal',
                'http_code' => $httpCode,
                'raw_response' => $response,
            ];
        }

        $result['_http_code'] = $httpCode;

        return $result;
    }
}