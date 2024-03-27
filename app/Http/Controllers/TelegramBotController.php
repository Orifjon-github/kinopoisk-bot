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
            array($telegram->buildInlineKeyBoardButton("Button 1", $url= "https://link1.com"), $telegram->buildInlineKeyBoardButton("Button 2", $url= "https://link2.com")),
            //Second row
            array($telegram->buildInlineKeyBoardButton("Button 3", $url= "https://link3.com"), $telegram->buildInlineKeyBoardButton("Button 4", $url= "https://link4.com"), $telegram->buildInlineKeyBoardButton("Button 5", $url= "https://link5.com")),
            //Third row
            array($telegram->buildInlineKeyBoardButton("Button 6", $url= "https://link6.com")) );
        $keyb = $telegram->buildInlineKeyBoard($option);
        $content = array('chat_id' => $chat_id, 'reply_markup' => $keyb, 'text' => "This is a Keyboard Test");
        $telegram->sendMessage($content);
    }
}
