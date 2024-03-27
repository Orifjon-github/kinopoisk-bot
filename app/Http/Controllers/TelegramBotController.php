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
                $content1 = ["chat_id" => "-1001987932786", "user_id" => $user_id];
                $result1 = $telegram->getChatMember($content1);
                $content2 = ["chat_id" => "-1001987932786", "user_id" => $user_id];
                $result2 = $telegram->getChatMember($content2);
                if ($result1["ok"] && $result2['ok']) {
                    $telegram->sendMessage(['chat_id' => $chat_id, 'text' => "Botdan bemalol foydalanishingiz mumkin"]);
                    exit();
                }
                $this->error($telegram, $chat_id);
            }
        } else {
            $this->error($telegram, $chat_id);
        }
    }

    private function error(Telegram $telegram, $chat_id) {
        $option = array(
            array($telegram->buildInlineKeyBoardButton("1 - kanal", $url= "https://t.me/+Z9QnOES4AkphNGYy"), $telegram->buildInlineKeyBoardButton("2 - kanal", $url= "https://t.me/orifjon_orifov"), $telegram->buildInlineKeyBoardButton("Tekshirish ✅", "", "check"))
        );

        $keyb = $telegram->buildInlineKeyBoard($option);
        $content = array('chat_id' => $chat_id, 'reply_markup' => $keyb, 'text' => "❌ Kechirasiz botimizdan foydalanishdan oldin ushbu kanallarga a'zo bo'lishingiz kerak.");
        $telegram->sendMessage($content);
    }
}
