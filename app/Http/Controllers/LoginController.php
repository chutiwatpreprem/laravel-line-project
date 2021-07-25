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
            // if ($event['type'] != 'message') continue;
            // $messageType = $event['message']['type'];
            // $message = $event['message']['text'];
            // $uid = $event['source']['userId'];
            // if ($messageType != 'text') continue;
            // //$match = preg_match('/Gift|gift|กิ๊ฟ/', $message);
            // //if (!$match) continue;
            // $response = $bot->replyText($event['replyToken'], $message . '</br>' . $uid);
            // if ($response->isSucceeded()) {
            //     logger('reply successfully');
            //     return;
            // }

            // 不是訊息的event先不處理
            if ($event['type'] != 'message') continue;
            $messageType = $event['message']['type'];
            $message = $event['message']['text'];

            // 不是文字訊息的類型先不處理
            if ($messageType != 'text') continue;
            $match = preg_match('/台灣|臺灣|Taiwan|taiwan/', $message);
            if (!$match) continue;

            $messageBuilder = new \LINE\LINEBot\MessageBuilder\MultiMessageBuilder();

            // 回覆文字
            $text = new LINEBot\MessageBuilder\TextMessageBuilder('南波萬');
            $messageBuilder->add($text);

            // 回覆貼圖
            $sticker = new LINEBot\MessageBuilder\StickerMessageBuilder('11537', '52002734');
            $messageBuilder->add($sticker);

            // 回覆地標
            $location = new LINEBot\MessageBuilder\LocationMessageBuilder('台灣南波萬', '哇呆灣郎啦', '24.147666', '120.673552');
            $messageBuilder->add($location);

            // 回覆相片訊息
            $image = new LINEBot\MessageBuilder\ImageMessageBuilder(
                'https://images.news18.com/ibnlive/uploads/2021/06/1622715559_disha.jpg?impolicy=website&width=510&height=356',
                'https://images.news18.com/ibnlive/uploads/2021/06/1622715559_disha.jpg?impolicy=website&width=510&height=356'
            );
            $messageBuilder->add($image);

            $response = $bot->replyMessage($event['replyToken'], $messageBuilder);
            if ($response->isSucceeded()) {
                logger('reply sticker successfully');
            } else {
                Log::warning($response->getRawBody());
                Log::warning('reply sticker failure');
            }
        }
        return $this->http200('anchor');
    }

    public function notiapi()
    {
        // จากใน Controller หรือที่อื่น ๆ
        //Line::send('ทดสอบส่งข้อความ');
        Line::imagePath(public_path('/upload/pic1.png'))->send('message22');
        Line::sticker(1, 138)
            ->send('ข้อความและสติกเกอร์');
    }

    public function pushmsg(Request $request)
    {
        $httpClient = new CurlHTTPClient(env('LINE_BOT_CHANNEL_TOKEN'));
        $bot = new LINEBot($httpClient, [
            'channelSecret' => env('LINE_BOT_CHANNEL_SECRET')
        ]);

        $textMessageBuilder = new \LINE\LINEBot\MessageBuilder\TextMessageBuilder($request['name']);
        $response = $bot->pushMessage('U3dec64d956dfd6c925a98188f83fd37b', $textMessageBuilder);

        echo $response->getHTTPStatus() . ' ' . $response->getRawBody();
    }
}
