<?php

namespace App\Traits;

trait Telegram
{
    public function sendMessage($method, $data = [])
    {
        $telegram_bot_token = env('TELEGRAM_BOT_TOKEN');
        $url = "https://api.telegram.org/bot" . $telegram_bot_token . "/" . $method;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        $res = curl_exec($ch);
        return json_decode($res);
    }
}