<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;
use LINE\LINEBot;
use LINE\LINEBot\HTTPClient\CurlHTTPClient;
use Log;
use Modules\Line\Constant\LineHookHttpResponse;
use Phattarachai\LineNotify\Facade\Line;

class LoginController extends Controller
{
    public function login()
    {
        return Socialite::driver('line-login')->with([
            'prompt' => 'consent',
            'bot_prompt' => 'normal',
        ])->redirect();
    }

    public function callback(Request $request)
    {
        if ($request->missing('code')) {
            dd($request);
        }

        /**
         * @var \Laravel\Socialite\Two\User
         */
        $user = Socialite::driver('line-login')->user();

        $loginUser = User::updateOrCreate([
            'line_id' => $user->id,
        ], [
            'name' => 'User', //$user->nickname,
            'avatar' => $user->avatar,
            'access_token' => $user->token,
            'refresh_token' => $user->refreshToken,
        ]);

        auth()->login($loginUser, true);

        return redirect(RouteServiceProvider::HOME);
    }

    public function logout()
    {
        auth()->logout();

        return redirect('/');
    }

    public function test(Request $request)
    {
        $httpClient = new CurlHTTPClient(env('LINE_BOT_CHANNEL_TOKEN'));
        $bot = new LINEBot($httpClient, [
            'channelSecret' => env('LINE_BOT_CHANNEL_SECRET')
        ]);

        $signature = $request->header(LINEBot\Constant\HTTPHeader::LINE_SIGNATURE);
        if (!$signature) {
            return $this->http403(LineHookHttpResponse::SIGNATURE_INVALID);
        }

        try {
            $bot->parseEventRequest($request->getContent(), $signature);
        } catch (LINEBot\Exception\InvalidSignatureException $exception) {
            return $this->http403(LineHookHttpResponse::SIGNATURE_INVALID);
        } catch (LINEBot\Exception\InvalidEventRequestException $exception) {
            return $this->http403(LineHookHttpResponse::EVENTS_INVALID);
        }

        $events = $request->events;
        foreach ($events as $event) {
            if ($event['type'] != 'message') continue;
            $messageType = $event['message']['type'];
            $message = $event['message']['text'];
            $uid = $event['source']['userId'];
            if ($messageType != 'text') continue;
            //$match = preg_match('/Gift|gift|????????????/', $message);
            //if (!$match) continue;
            $response = $bot->replyText($event['replyToken'], $message . '</br>' . $uid);
            if ($response->isSucceeded()) {
                logger('reply successfully');
                return;
            }

            // ???????????????event????????????
            // if ($event['type'] != 'message') continue;
            // $messageType = $event['message']['type'];
            // $message = $event['message']['text'];

            // // ???????????????????????????????????????
            // if ($messageType != 'text') continue;
            // $match = preg_match('/??????|??????|Taiwan|taiwan/', $message);
            // if (!$match) continue;

            // $messageBuilder = new \LINE\LINEBot\MessageBuilder\MultiMessageBuilder();

            // // ????????????
            // $text = new LINEBot\MessageBuilder\TextMessageBuilder('?????????');
            // $messageBuilder->add($text);

            // // ????????????
            // $sticker = new LINEBot\MessageBuilder\StickerMessageBuilder('11537', '52002734');
            // $messageBuilder->add($sticker);

            // // ????????????
            // $location = new LINEBot\MessageBuilder\LocationMessageBuilder('???????????????', '???????????????', '24.147666', '120.673552');
            // $messageBuilder->add($location);

            // // ??????????????????
            // $image = new LINEBot\MessageBuilder\ImageMessageBuilder(
            //     'https://images.news18.com/ibnlive/uploads/2021/06/1622715559_disha.jpg',
            //     'https://www.seoclerk.com/pics/000/940/831/fb9b15c1ad6730d8a2ee2c326afbcd27.png'
            // );
            // $messageBuilder->add($image);

            // $response = $bot->replyMessage($event['replyToken'], $messageBuilder);
            // if ($response->isSucceeded()) {
            //     logger('reply sticker successfully');
            // } else {
            //     Log::warning($response->getRawBody());
            //     Log::warning('reply sticker failure');
            // }
        }
        return $this->http200('anchor');
    }

    public function notiapi()
    {
        // ??????????????? Controller ????????????????????????????????? ???
        //Line::send('?????????????????????????????????????????????');
        Line::imagePath(public_path('/upload/pic1.png'))->send('message22');
        Line::sticker(1, 138)
            ->send('?????????????????????????????????????????????????????????');
    }

    public function pushmsg(Request $request)
    {
        $httpClient = new CurlHTTPClient(env('LINE_BOT_CHANNEL_TOKEN'));
        $bot = new LINEBot($httpClient, [
            'channelSecret' => env('LINE_BOT_CHANNEL_SECRET')
        ]);

        $textMessageBuilder = new \LINE\LINEBot\MessageBuilder\TextMessageBuilder($request['name']);
        $response = $bot->pushMessage(env('LINE_NOTIFY_CLIENT_ID'), $textMessageBuilder);

        echo $response->getHTTPStatus() . ' ' . $response->getRawBody();
    }
}
