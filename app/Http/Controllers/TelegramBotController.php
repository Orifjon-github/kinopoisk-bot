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
     $chat_id = $result['message'] ['chat']['id'];
     $text = $result['message'] ['text'];
     if ($text == '/start') {
         Step::set_step(Step::STEP_START);
     } else {
         Step::set_step(Step::STEP_SEARCH);
     }

     switch (Step::get_step()) {
         case Step::STEP_START:
             $content = array('chat_id' => $chat_id, 'text' => 'Assalomu alaykum. Botimizga xush kelibsiz!!! Qidirayotgan kinoyingizni bir zumda topamiz. Shunchaki qidiring...');
             $telegram->sendMessage($content);
             break;
         case Step::STEP_SEARCH:
             $movies = Movies::all();
             $result = array();
             $no = 1;
             foreach ($movies as $movie) {
                 $result[$no] = $movie->name;
                 $no++;
                 foreach ($result as $key => $value) {
                     $text = ''.$key.'. '.$value;
                 }
             }
             $content = array('chat_id' => $chat_id, 'text' => $text);
             $telegram->sendMessage($content);
             break;
         default:
             $content = array('chat_id' => $chat_id, 'text' => 'switch default');
             $telegram->sendMessage($content);

     }
 }
}
