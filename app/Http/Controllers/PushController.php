<?php

namespace App\Http\Controllers;

use Firebase\JWT\JWT;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

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
        // Lưu token vào database (ví dụ: bảng 'subscriptions')
        DB::table('subscriptions')->updateOrInsert(
            ['token' => $token],
            ['created_at' => now()]
        );
        return response()->json(['message' => 'Subscription saved']);
    }

    public function sendPush()
    {
        $serviceAccount = public_path('firebase-credentials.json');
        $credentials = json_decode(file_get_contents($serviceAccount), true);

        $accessToken = Cache::remember('fcm_access_token', 3500, function () use ($credentials) {
            $jwt = $this->generateJwt($credentials);
            return $this->getAccessToken($jwt);
        });

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
                'headers' => $headers,
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
