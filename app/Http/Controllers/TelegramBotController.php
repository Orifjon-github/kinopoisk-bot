<?php

namespace App\Http\Controllers;

use App\Models\Movies;

class TelegramBotController extends Controller
{
    const ADMIN_CHAT_ID = 298410462;
    const REQUIRED_CHANNEL_1 = "-1001513289865";
    const REQUIRED_CHANNEL_2 = "-1001987932786";

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

        if (isset($data['message']['text']) && $data['message']['text'] == '/start') {
            if ($this->check($telegram, $chat_id, $user_id)) {
                $telegram->sendMessage(['chat_id' => $chat_id, 'text' => "Xush kelibsiz! Botdan to'liq foydalanishingiz mumkin! Kino kodini yuboring!"]);
                exit();
            }
        }
        $callback_query = $telegram->Callback_Query();
        if (!empty($callback_query)) {
            $callback_data = $telegram->Callback_Data();
            if ($callback_data == 'check') {
                if ($this->check($telegram, $chat_id, $user_id)) {
                    $telegram->sendMessage(['chat_id' => $chat_id, 'text' => "Xush kelibsiz! Botdan to'liq foydalanishingiz mumkin! Kino kodini yuboring!"]);
                    exit();
                }
            }
        }
        if (isset($data['message']['text']) && is_numeric($data['message']['text']) && $data['message']['text'] < 1000) {
            if ($this->check($telegram, $chat_id, $user_id)) {
                $movie = Movies::where('code', $data['message']['text'])->first() ?? null;
                if ($movie) {
                    $telegram->sendVideo(['chat_id' => $chat_id, 'video' => $movie->file_id, 'caption' => $movie->caption]);
                } else {
                    $telegram->sendMessage(['chat_id' => $chat_id, 'text' => "Ushbu kod bo'yicha hech qanday kino topilmadi ❌"]);
                }
                exit();
            }
        }
    }

    private function error(Telegram $telegram, $chat_id, $answer = false)
    {
        $option = array(
            array($telegram->buildInlineKeyBoardButton("1 - kanal", $url = "https://t.me/+Z9QnOES4AkphNGYy")),
            array($telegram->buildInlineKeyBoardButton("2 - kanal", $url = "https://t.me/orifjon_orifov")),
            array($telegram->buildInlineKeyBoardButton("Tekshirish ✅", "", "check"))
        );

        $keyb = $telegram->buildInlineKeyBoard($option);
        $content = array('chat_id' => $chat_id, 'reply_markup' => $keyb, 'text' => "❌ Kechirasiz botimizdan foydalanishdan oldin ushbu kanallarga a'zo bo'lishingiz kerak.");
        $telegram->sendMessage($content);
        exit();
    }

    private function check(Telegram $telegram, $chat_id, $user_id) {
        $content1 = ["chat_id" => self::REQUIRED_CHANNEL_1, "user_id" => $user_id];
        $result1 = $telegram->getChatMember($content1);
        $content2 = ["chat_id" => self::REQUIRED_CHANNEL_2, "user_id" => $user_id];
        $result2 = $telegram->getChatMember($content2);

        if ($result1['ok'] && $result2['ok']) {
            if ($result1['result']['status'] == "member" && $result2['result']['status'] == "member") {
                return true;
            } else {
                $this->error($telegram, $chat_id);
            }
        } else {
            $telegram->sendMessage(['chat_id' => self::ADMIN_CHAT_ID, 'text' => "🆘🆘🆘🆘🆘🆘🆘🆘🆘 \n Kanallardan biri botni adminlikdan chiqardi. Zudlik bilan bu muammoni hal qiling. Hozirda hech kim botdan foydalana olmayapti ❌"]);
            $telegram->sendMessage(['chat_id' => self::ADMIN_CHAT_ID, 'text' => json_encode([$result1, $result2])]);
            $this->error($telegram, $chat_id);
        }
    }
}
