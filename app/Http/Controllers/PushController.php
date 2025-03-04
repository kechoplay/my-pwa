<?php

namespace App\Http\Controllers;

use Firebase\JWT\JWT;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

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
            'iat' => time(),
            'exp' => time() + 3600,
        ])));
        $signatureInput = "$header.$payload";

        if (!openssl_sign($signatureInput, $signature, $credentials['private_key'], 'sha256')) {
            throw new \Exception('Failed to sign JWT: ' . openssl_error_string());
        }
        $signature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));

        return "$signatureInput.$signature";
    }

    private function getAccessToken($jwt)
    {
        $response = Http::asForm()->post('https://oauth2.googleapis.com/token', [
            'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
            'assertion' => $jwt,
        ]);

        return $response->json()['access_token'];
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
        $client = new \Google_Client();
        $client->setAuthConfig(public_path('firebase-credentials.json')); // Đường dẫn đến tệp JSON
        $client->addScope('https://www.googleapis.com/auth/firebase.messaging');

        $client->setAccessType('offline');
        $client->fetchAccessTokenWithAssertion();
        $tokenArray = $client->getAccessToken();
        $tokenArray['created'] = time();
        $client->setAccessToken($tokenArray);
        $accessToken = $tokenArray['access_token'];

        $tokens = DB::table('subscriptions')->pluck('token')->toArray();
        if (empty($tokens)) {
            return 'No tokens available';
        }

        $headers = [
            'Authorization' => 'Bearer ' . $accessToken,
            'Content-Type' => 'application/json',
        ];
        $client = new Client();
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
                $headers,
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
