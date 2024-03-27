<?php

namespace App\Http\Controllers;

use function PHPUnit\Framework\callback;

class TelegramBotController extends Controller
{
    public function start()
    {
        $telegram = new Telegram(env('TELEGRAM_BOT_TOKEN'));
        $result = $telegram->getData();
        $chat_id = $telegram->ChatID();
        $option = array(
            array($telegram->buildInlineKeyBoardButton("1 - kanal", $url= "https://t.me/+Z9QnOES4AkphNGYy"), $telegram->buildInlineKeyBoardButton("2 - kanal", $url= "https://t.me/orifjon_orifov"), $telegram->buildInlineKeyBoardButton("Tekshirish ✅", $callback_data = "Tekshirish"))
        );

        $keyb = $telegram->buildInlineKeyBoard($option);
        $content = array('chat_id' => $chat_id, 'reply_markup' => $keyb, 'text' => "❌ Kechirasiz botimizdan foydalanishdan oldin ushbu kanallarga a'zo bo'lishingiz kerak.");
        $telegram->sendMessage($content);
        $content = array('chat_id' => $chat_id, 'text' => json_encode($result, JSON_UNESCAPED_UNICODE));
        $telegram->sendMessage($content);
    }
}
