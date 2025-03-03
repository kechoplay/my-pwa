<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;

class PushController extends Controller
{
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
        $factory = (new Factory)->withServiceAccount(public_path('firebase-credentials.json'));
        $messaging = $factory->createMessaging();
        $tokens = DB::table('subscriptions')->pluck('token')->toArray();
        if (empty($tokens)) return 'No subscribers';

        $message = CloudMessage::new()
            ->withNotification([
                'title' => 'Hello from Laravel!',
                'body' => 'This is a Firebase push notification.'
            ]);

        $response = $messaging->sendMulticast($message, $tokens);
        return 'Push sent!';
    }
}
