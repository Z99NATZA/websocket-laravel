<?php

namespace App\Http\Controllers\Chat;

use App\Events\Chat\ChatEvent;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ChatController extends Controller
{
    public function chat(Request $request)
    {
        $roomCode = $request->input('roomCode');
        $message = $request->input('message');

        $cacheKey = "chat_room_{$roomCode}";
        $messages = Cache::get($cacheKey, []);
        $messages[] = $message;
        Cache::put($cacheKey, $messages, 600);

        $data = ['message' => $message, 'messages' => $messages];

        event(new ChatEvent($roomCode, $data));
    }
    
    public function clear(Request $request)
    {
        $roomCode = $request->input('roomCode');

        $cacheKey = "chat_room_{$roomCode}";
        Cache::forget($cacheKey);

        $data = ['message' => 'Chat cleared', 'messages' => []];

        event(new ChatEvent($roomCode, $data));
    }
}
