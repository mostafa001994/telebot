<?php

class Telegram
{

public static function call($method, $data = [])
{
    $url = "https://api.telegram.org/bot" . BOT_TOKEN . "/" . $method;

    $ch = curl_init($url);

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

    $result = curl_exec($ch);

    $error = curl_error($ch);

    curl_close($ch);


    file_put_contents(
        __DIR__ . '/../telegram_response.log',
        date('Y-m-d H:i:s') . "\n" .
        "URL: " . $url . "\n" .
        "DATA:\n" . print_r($data, true) .
        "\nRESPONSE:\n" . $result .
        "\nCURL ERROR:\n" . $error .
        "\n----------------\n",
        FILE_APPEND
    );


    return json_decode($result, true);
}



    public static function sendMessage(
        $chatId,
        $text,
        $keyboard = null
    ) {

        $data = [

            'chat_id' => $chatId,

            'text' => $text,

        ];

        if ($keyboard) {

            $data['reply_markup'] =
                json_encode($keyboard);
        }

        return self::call(
            "sendMessage",
            $data
        );
    }





    public static function answerCallbackQuery($callbackQueryId, $text = null, $showAlert = false)
    {
        $data = [
            'callback_query_id' => $callbackQueryId,
        ];

        if ($text) {
            $data['text'] = $text;
        }

        if ($showAlert) {
            $data['show_alert'] = true;
        }

        return self::call(
            "answerCallbackQuery",
            $data
        );
    }






}