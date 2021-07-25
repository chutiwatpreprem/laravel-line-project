<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;
use LINE\LINEBot;
use LINE\LINEBot\HTTPClient\CurlHTTPClient;
use Modules\Line\Constant\LineHookHttpResponse;

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

            $response = $bot->replyText($event['replyToken'], 'ว่าไง...' . $events);
            if ($response->isSucceeded()) {
                logger('reply successfully');
                return;
            }

            // if ($event['type'] != 'message') continue;
            // $messageType = $event['message']['type'];
            // $message = $event['message']['text'];

            // if ($messageType != 'text') continue;
            // $match = preg_match('/Gift|gift|กิ๊ฟ/', $message);
            // if (!$match) continue;
            // $response = $bot->replyText($event['replyToken'], 'ว่าไง...' . $message);
            // if ($response->isSucceeded()) {
            //     logger('reply successfully');
            //     return;
            // }
        }
        return $this->http200('anchor');
    }
}
