<?php

namespace App\Http\Controllers;

use App\Models\Channel;
use App\Models\Movies;
use App\Models\User;

class TelegramBotController extends Controller
{
    public function __construct() {
        $this->admin = env('ADMIN_CHAT_ID', 298410462);
    }

    public function start()
    {
        $telegram = new Telegram(env('TELEGRAM_BOT_TOKEN'));
        $chat_id = $telegram->ChatID();
        $user_id = $telegram->UserID();
        $data = $telegram->getData();
        if ($chat_id == $this->admin) {
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
            $user = User::where('chat_id', $data['message']['from']['id'])->first() ?? null;
            if (!$user) {
                $user = new User();
                $user->name = $data['message']['from']['first_name'] ?? null;
                $user->chat_id = $data['message']['from']['id'] ?? null;
                $user->username = $data['message']['from']['username'] ?? null;
                $user->save();
            }
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
                    $movie->count = (int)$movie->count + 1;
                    $movie->save();
                    $telegram->sendVideo(['chat_id' => $chat_id, 'video' => $movie->file_id, 'caption' => $movie->caption . "\n\n Yuklab olishlar soni: " . $movie->count]);
                } else {
                    $telegram->sendMessage(['chat_id' => $chat_id, 'text' => "Ushbu kod bo'yicha hech qanday kino topilmadi ❌"]);
                }
                exit();
            }
        }
    }

    private function error(Telegram $telegram, $chat_id, $answer = false)
    {
        $option = [];
        $channels = Channel::all();
        foreach ($channels as $channel) {
            $option[] = array($telegram->buildInlineKeyBoardButton($channel->name, $channel->link));
        }
        $option[] =  array($telegram->buildInlineKeyBoardButton("Tekshirish ✅", "", "check"));
        $keyb = $telegram->buildInlineKeyBoard($option);
        $content = array('chat_id' => $chat_id, 'reply_markup' => $keyb, 'text' => "❌ Kechirasiz botimizdan foydalanishdan oldin ushbu kanallarga a'zo bo'lishingiz kerak.");
        $telegram->sendMessage($content);
        exit();
    }

    private function check(Telegram $telegram, $chat_id, $user_id) {
        $channels = Channel::all();
        foreach ($channels as $channel) {
            $content = ["chat_id" => $channel->chat_id, "user_id" => $user_id];
            $result = $telegram->getChatMember($content);
            $telegram->sendMessage(['chat_id' => $this->admin, 'text' => json_encode($result, JSON_UNESCAPED_UNICODE)]);
            if ($result['ok']) {
                if (in_array($result['result']['status'], ['member', 'creator', 'administrator'])) {
                    return true;
                } elseif ($result['result']['status'] == 'left') {
                    $this->error($telegram, $chat_id);
                } else {
                    $telegram->sendMessage(['chat_id' => $this->admin, 'text' => 'New status: ' . $result['result']['status']]);
                    $this->error($telegram, $chat_id);
                }
            } else {
                $telegram->sendMessage(['chat_id' => $this->admin, 'text' => "🆘🆘🆘🆘🆘🆘🆘🆘🆘 \n Kanal botni adminlikdan chiqargan bo'lishi mumkin. Zudlik bilan bu muammoni hal qiling. Hozirda hech kim botdan foydalana olmayapti ❌ \n\n Channel ID: " . $channel->id . "\n" . "Channel link: " . $channel->link]);
                $telegram->sendMessage(['chat_id' => $this->admin, 'text' => json_encode($result)]);
                $this->error($telegram, $chat_id);
            }
        }
        return false;
    }
}
