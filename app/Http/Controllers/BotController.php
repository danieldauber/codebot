<?php

namespace App\Http\Controllers;

use CodeBot\CallSendApi;
use CodeBot\WebHook;
use CodeBot\SenderRequest;
use CodeBot\Message\Text;
use CodeBot\Element\Button;
use CodeBot\Element\Product;
use CodeBot\TemplatesMessage\ButtonsTemplate;
use CodeBot\TemplatesMessage\GenericTemplate;
use CodeBot\TemplatesMessage\ListTemplate;
use Illuminate\Http\Request;
use CodeBot\Build\Solid;

class BotController extends Controller
{

    public function subscribe()
    {
        $webhook = new WebHook;
        $subscribe = $webhook->check(config('botfb.validationToken'));
        if (!$subscribe) {
            abort(403, 'Unauthorized action');
        }
        return $subscribe;
    }

    public function receiveMessage(Request $request)
    {
        $sender = new SenderRequest;
        $senderId = $sender->getSenderId();
        $message = $sender->getMessage();
        $postback = $sender->getPostBack();

        $bot = Solid::factory();
        Solid::pageAccessToken(config('botfb.pageAccessToken'));
        Solid::setSender($senderId);

        if ($postback) {
            if (is_array($postback)) {
                $postback = json_encode($postback);
            }
            $bot->message('text', 'Voce chamou o postback' . $postback);
            return '';
        }

        $bot->message('text', 'Oi, eu sou o Bot');
        $bot->message('text', 'Você digitou: ' . $message);

        $buttons = [
            new Button('web_url', 'Google', 'https://www.google.com')
        ];
        $bot->template('buttons', 'Abrir o link', $buttons);

        $bot->message('image', 'https://assets.b9.com.br/wp-content/uploads/2015/08/kuat-gif.gif');
        $bot->message('audio', 'http://www.portalqualis.com.br/hospital/assets/sounds/warning.mp3');
        $bot->message('file', 'https://www.portalqualis.com.br/hospital/assets/manual/manual.pdf');
        $bot->message('video', 'http://www.portalqualis.com.br/hospital/assets/videos/bacteriologia.mp4');

        $products = [
            new Product(
                'Produto 1',
                'https://assets.b9.com.br/wp-content/uploads/2015/08/kuat-gif.gif',
                'Google',
                new Button('web_url', 'Google', 'https://www.google.com')
            ),
            new Product(
                'Produto 1',
                'https://assets.b9.com.br/wp-content/uploads/2015/08/kuat-gif.gif',
                'Google',
                new Button('web_url', 'Google', 'https://www.google.com')
            )
        ];

        $bot->template('generic', '', $products);
        // $bot->template('list', '', $products);

        return '';
    }
}
