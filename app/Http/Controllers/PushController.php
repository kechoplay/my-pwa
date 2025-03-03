<?php

namespace App\Http\Controllers;

use Firebase\JWT\JWT;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PushController extends Controller
{
    private function getAccessToken()
    {
        $credentials = json_decode(file_get_contents(public_path('firebase-credentials.json')), true);
        $clientEmail = $credentials['client_email'];
        $privateKey = $credentials['private_key'];

        // Tạo JWT
        $now = time();
        $payload = [
            'iss' => $clientEmail, // Issuer (client email từ service account)
            'sub' => $clientEmail, // Subject
            'aud' => 'https://oauth2.googleapis.com/token', // Audience
            'iat' => $now, // Issued at
            'exp' => $now + 3600, // Expiration (1 giờ)
            'scope' => 'https://www.googleapis.com/auth/firebase.messaging' // Scope cho FCM
        ];

        $jwt = JWT::encode($payload, $privateKey, 'RS256');
        // Gửi yêu cầu lấy access token
        $client = new Client();
        $response = $client->post('https://oauth2.googleapis.com/token', [
            'form_params' => [
                'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                'assertion' => $jwt,
            ],
        ]);

        $data = json_decode($response->getBody()->getContents(), true);
        return $data['access_token'];
    }

    public function subscribe(Request $request)
    {
        $token = $request->input('token');
        // Lưu token vào database (ví dụ: bảng 'subscriptions')
        DB::table('subscriptions')->updateOrInsert(
            ['token' => $token],
            ['created_at' => now()]
        );
        return response()->json(['message' => 'Subscription saved']);
    }

    public function sendPush()
    {
        $tokens = DB::table('subscriptions')->pluck('token')->toArray();
        if (empty($tokens)) {
            return 'No tokens available';
        }

        $client = new Client();
        $accessToken = $this->getAccessToken();
        $projectId = env('FIREBASE_PROJECT_ID');
        $url = "https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send";

        $payload = [
            'message' => [
                'token' => $tokens[0], // Gửi đến token đầu tiên, có thể lặp qua tất cả
                'notification' => [
                    'title' => 'Test from Laravel',
                    'body' => 'This is a push via HTTP v1!'
                ]
            ]
        ];

        try {
            $response = $client->post($url, [
                'headers' => [
                    'Authorization' => "Bearer $accessToken",
                    'Content-Type' => 'application/json',
                ],
                'json' => $payload,
            ]);
            return $response->getBody()->getContents();
        } catch (\Exception $e) {
            return 'Error: ' . $e->getMessage();
        } catch (GuzzleException $e) {
            return 'Error: ' . $e->getMessage();
        }
    }
}
