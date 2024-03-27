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
        $user_id = $telegram->UserID();

        $callback_query = $telegram->Callback_Query();
        if (!empty($callback_query)) {
            $callback_data = $telegram->Callback_Data();
            if ($callback_data == 'check') {
                $content = ["chat_id" => "-1001987932786", "user_id" => $user_id];
                $result = $telegram->getChatMember($content);
                $telegram->sendMessage(['chat_id' => $chat_id, 'text' => json_encode($result, JSON_UNESCAPED_UNICODE)]);
            }
        } else {
            $option = array(
                array($telegram->buildInlineKeyBoardButton("1 - kanal", $url= "https://t.me/+Z9QnOES4AkphNGYy"), $telegram->buildInlineKeyBoardButton("2 - kanal", $url= "https://t.me/orifjon_orifov"), $telegram->buildInlineKeyBoardButton("Tekshirish ✅", "", "check"))
            );

            $keyb = $telegram->buildInlineKeyBoard($option);
            $content = array('chat_id' => $chat_id, 'reply_markup' => $keyb, 'text' => "❌ Kechirasiz botimizdan foydalanishdan oldin ushbu kanallarga a'zo bo'lishingiz kerak.");
            $telegram->sendMessage($content);
        }

    }
}
