<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Kreait\Firebase\Exception\FirebaseException;
use Kreait\Firebase\Exception\MessagingException;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\MessageTarget;
use Kreait\Firebase\Messaging\Notification;

class PushController extends Controller
{
    /**
     * @throws \Exception
     */
    private function generateJwt($credentials)
    {
        $header = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode(json_encode([
            'alg' => 'RS256',
            'typ' => 'JWT'
        ])));
        $payload = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode(json_encode([
            'iss' => $credentials['client_email'],
            'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
            'aud' => 'https://oauth2.googleapis.com/token',
            'iat' => Carbon::now()->timestamp,
            'exp' => Carbon::now()->timestamp + 3600,
        ])));
        $signatureInput = "$header.$payload";

        if (!openssl_sign($signatureInput, $signature, $credentials['private_key'], 'sha256')) {
            throw new \Exception('Failed to sign JWT: ' . openssl_error_string());
        }
        $signature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));

        return "$signatureInput.$signature";
    }

    /**
     * @throws GuzzleException
     */
    private function getAccessToken($jwt)
    {
        $response = Http::asForm()->post('https://oauth2.googleapis.com/token', [
            'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
            'assertion' => $jwt,
        ]);

        if (!$response->successful()) {
            throw new \Exception('Failed to get access token: ' . $response->body());
        }

        $data = $response->json();
        if (!isset($data['access_token'])) {
            throw new \Exception('No access token in response: ' . json_encode($data));
        }

        return $data['access_token'];
    }

    public function subscribe(Request $request)
    {
        $token = $request->input('token');
        DB::table('subscriptions')->updateOrInsert(
            ['token' => $token],
            ['created_at' => now()]
        );
        return response()->json(['message' => 'Subscription saved']);
    }

    /**
     * @throws \Exception
     */
    public function sendPush()
    {
        $factory = (new Factory())->withServiceAccount(public_path('firebase-credentials.json'));
        $messaging = $factory->createMessaging();

        $tokens = DB::table('subscriptions')->pluck('token')->toArray();
        if (empty($tokens)) return 'No subscribers';

        foreach ($tokens as $token) {
            $notification = Notification::create('Hello from Laravel!', 'This is a push notification with kreait/firebase-php 7.10');
            $message = CloudMessage::withTarget('token', $token)
                ->withNotification($notification);

            try {
                $report = $messaging->send($message);
                dd($report);
            } catch (MessagingException $e) {
                Log::debug($e);
                return $e;
            } catch (FirebaseException $e) {
                Log::debug($e);
                return $e;
            }
        }

        return 'Push sent!';
    }
}
