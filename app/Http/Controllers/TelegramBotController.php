<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Telegram;

class TelegramBotController extends Controller
{
 public function search() {
     $telegram = new Telegram(env('TELEGRAM_BOT_TOKEN'));
     $result = $telegram->getData();
     $text = $result['message'] ['text'];
     $chat_id = $result['message'] ['chat']['id'];
     $content = array('chat_id' => $chat_id, 'text' => 'Test');
     $telegram->sendMessage($content);
 }
}
