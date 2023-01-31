<?php

namespace App\Http\Controllers;

use App\Helpers\Step;
use App\Models\Movies;
use Illuminate\Http\Request;
use App\Http\Controllers\Telegram;

class TelegramBotController extends Controller
{
 public function start() {
     $telegram = new Telegram(env('TELEGRAM_BOT_TOKEN'));
     $result = $telegram->getData();
     $chat_id = $telegram->ChatID();
     $type = $result['message'];
     if (array_key_exists('text', $type)) {
         $content = array('chat_id' => $chat_id, 'text' => 'text');
     } elseif (array_key_exists('video', $type)) {
         $content = array('chat_id' => $chat_id, 'text' => 'video');
     } else {
         $content = array('chat_id' => $chat_id, 'text' => 'text va video emas');
     }
     $telegram->sendMessage($content);
 }
}
