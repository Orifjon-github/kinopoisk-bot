<?php

namespace App\Http\Controllers;

use App\Models\Movies;

class TelegramBotController extends Controller
{
    const ADMIN_CHAT_ID = 298410462;

    public function start()
    {
        $telegram = new Telegram(env('TELEGRAM_BOT_TOKEN'));
        $chat_id = $telegram->ChatID();
        $user_id = $telegram->UserID();
        $data = $telegram->getData();
        if ($chat_id == self::ADMIN_CHAT_ID) {
            if (isset($data['message']['text']) && $data['message']['text'] == '/start') {
                  $telegram->sendMessage(['chat_id' => $chat_id, 'text' => "Xush kelibsiz! Kinoni yuboring."]);
                  exit();
            }
//            $telegram->sendMessage(['chat_id' => $chat_id, 'text' => json_encode($data)]);
            $file_id = $data['message']['video']['file_id'] ?? false;
            if ($file_id) {
                $movie = new Movies();
                $movie->file_id = $file_id;
                $movie->caption = $data['message']['caption'] ?? '';
                $movie->code = 'pending';
                $movie->save();
                $telegram->sendMessage(['chat_id' => $chat_id, 'text' => "Ushbu kino uchun kodni yuboring! (Faqat 1000 dan katta bo'lmagan raqamlarda)"]);
            } else {
                if (isset($data['message']['text']) && $data['message']['text'] < 1000) {
                    $lastMovie = Movies::latest()->first() ?? null;
                    if ($lastMovie && $lastMovie->code == 'pending') {
                        $lastMovie->code = $data['message']['text'];
                        $lastMovie->save();
                        $telegram->sendMessage(['chat_id' => $chat_id, 'text' => "Kino muvafaqqiyatli saqlandi ✅. Foydalanuvchi sifatida tekshirib ko'rishingiz mumkin! (Admin uchun bot boshqacha ishlaydi)"]);
                        exit();
                    }
                }
                $telegram->sendMessage(['chat_id' => $chat_id, 'text' => "Video yubormadingiz ❌"]);
            }
            exit();
        }
        $callback_query = $telegram->Callback_Query();
        if (!empty($callback_query)) {
            $callback_data = $telegram->Callback_Data();
            if ($callback_data == 'check') {
                $content1 = ["chat_id" => "-1001513289865", "user_id" => $user_id];
                $result1 = $telegram->getChatMember($content1);
                $content2 = ["chat_id" => "-1001987932786", "user_id" => $user_id];
                $result2 = $telegram->getChatMember($content2);

                if ($result1['ok'] && $result2['ok']) {
                    if ($result1['result']['status'] == "member" && $result2['result']['status'] == "member") {
                        $telegram->sendMessage(['chat_id' => $chat_id, 'text' => "Xush kelibsiz! Botdan to'liq foydalanishingiz mumkin!"]);
                    } else {
                        $this->error($telegram, $chat_id);
                    }
                } else {
                    $this->error($telegram, $chat_id);
                }
            }
        } else {
            $this->error($telegram, $chat_id);
        }
    }

    private function error(Telegram $telegram, $chat_id, $answer = false)
    {
        $option = array(
            array($telegram->buildInlineKeyBoardButton("1 - kanal (private)", $url = "https://t.me/+Z9QnOES4AkphNGYy")),
            array($telegram->buildInlineKeyBoardButton("2 - kanal (public)", $url = "https://t.me/orifjon_orifov")),
            array($telegram->buildInlineKeyBoardButton("Tekshirish ✅", "", "check"))
        );

        $keyb = $telegram->buildInlineKeyBoard($option);
        $content = array('chat_id' => $chat_id, 'reply_markup' => $keyb, 'text' => "❌ Kechirasiz botimizdan foydalanishdan oldin ushbu kanallarga a'zo bo'lishingiz kerak.");
        $telegram->sendMessage($content);
    }
}
