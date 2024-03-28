<?php

namespace App\Http\Controllers;

use function PHPUnit\Framework\callback;

class TelegramBotController extends Controller
{
    public function start()
    {
        $telegram = new Telegram(env('TELEGRAM_BOT_TOKEN'));
//        $result = $telegram->getData();
        $chat_id = $telegram->ChatID();
        $user_id = $telegram->UserID();

        $result = $telegram->sendMessage(['chat_id' => 't.me/eudhdhdhdhdhddh', 'text' => '1212']);
        $telegram->sendMessage(['chat_id' => $chat_id, 'text' => json_encode($result, JSON_UNESCAPED_UNICODE)]);
        $callback_query = $telegram->Callback_Query();
        if (!empty($callback_query)) {
            $callback_data = $telegram->Callback_Data();
            if ($callback_data == 'check') {
                $content1 = ["chat_id" => "-1002088735419", "user_id" => $user_id];
                $result1 = $telegram->getChatMember($content1);
                $content2 = ["chat_id" => "-1002086187280", "user_id" => $user_id];
                $result2 = $telegram->getChatMember($content2);
                $telegram->sendMessage(['chat_id' => $chat_id, 'text' => json_encode([$result1, $result2])]);
                exit();
//                $this->error($telegram, $chat_id);
            }
        } else {
            $this->error($telegram, $chat_id);
        }
    }

    private function error(Telegram $telegram, $chat_id) {
        $option = array(
            array($telegram->buildInlineKeyBoardButton("1 - kanal", $url= "https://t.me/eudhdhdhdhdhddh")),
            array($telegram->buildInlineKeyBoardButton("2 - kanal", $url= "https://t.me/+Svw6BvNlGU9hZmMy")),
            array($telegram->buildInlineKeyBoardButton("Tekshirish ✅", "", "check"))
        );

        $keyb = $telegram->buildInlineKeyBoard($option);
        $content = array('chat_id' => $chat_id, 'reply_markup' => $keyb, 'text' => "❌ Kechirasiz botimizdan foydalanishdan oldin ushbu kanallarga a'zo bo'lishingiz kerak.");
        $telegram->sendMessage($content);
    }
}
