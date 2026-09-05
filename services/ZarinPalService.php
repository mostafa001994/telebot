<?php

class ZarinPalService
{
    private string $merchantId;

    public function __construct()
    {
        $this->merchantId = "ZARINPAL_MERCHANT_ID";
    }

    public function request(int $amount, string $callbackUrl)
    {
        $data = [
            "merchant_id" => $this->merchantId,
            "amount" => $amount,
            "callback_url" => $callbackUrl,
            "description" => "Subscription Payment",
        ];

        $ch = curl_init("https://api.zarinpal.com/pg/v4/payment/request.json");

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);

        $result = json_decode(curl_exec($ch), true);

        curl_close($ch);

        return $result;
    }

    public function verify(int $amount, string $authority)
    {
        $data = [
            "merchant_id" => $this->merchantId,
            "amount" => $amount,
            "authority" => $authority,
        ];

        $ch = curl_init("https://api.zarinpal.com/pg/v4/payment/verify.json");

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);

        $result = json_decode(curl_exec($ch), true);

        curl_close($ch);

        return $result;
    }
}