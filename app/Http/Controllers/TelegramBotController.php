<?php

namespace App\Http\Controllers;

class TelegramBotController extends Controller
{
    public function start()
    {
        $telegram = new Telegram(env('TELEGRAM_BOT_TOKEN'));
        $result = $telegram->getData();
        $chat_id = $telegram->ChatID();
        $option = array(
            //First row
            array($telegram->buildInlineKeyBoardButton("1 - kanal", $url= "https://t.me/+Z9QnOES4AkphNGYy"), $telegram->buildInlineKeyBoardButton("2 - kanal", $url= "https://t.me/orifjon_orifov")),
        );

        $keyb = $telegram->buildInlineKeyBoard($option);
        $content = array('chat_id' => $chat_id, 'reply_markup' => $keyb, 'text' => "❌ Kechirasiz botimizdan foydalanishdan oldin ushbu kanallarga a'zo bo'lishingiz kerak.");
        $telegram->sendMessage($content);
    }
}
