<?php

/* cmd
----------------------------------------------------------------------------
php artisan install:broadcasting --pusher

app_id = "xxxx"
key = "xxxx"
secret = "xxxx"
cluster = "ap1"
----------------------------------------------------------------------------
*/


/* cmd
----------------------------------------------------------------------------
Would you like to install and build the Node dependencies required for broadcasting? (yes/no) [yes]
----------------------------------------------------------------------------
*/


/* cmd
----------------------------------------------------------------------------
composer require pusher/pusher-php-server
----------------------------------------------------------------------------
*/


/* .env
----------------------------------------------------------------------------
BROADCAST_CONNECTION=pusher

PUSHER_APP_ID=2077982
PUSHER_APP_KEY=7bdf5022d374d7bff9f7
PUSHER_APP_SECRET=37e4e879313dad3cd905
PUSHER_APP_CLUSTER=ap1
PUSHER_PORT=443
PUSHER_SCHEME=https

VITE_PUSHER_APP_KEY="${PUSHER_APP_KEY}"
VITE_PUSHER_APP_CLUSTER="${PUSHER_APP_CLUSTER}"
# VITE_PUSHER_HOST="${PUSHER_HOST}"
VITE_PUSHER_PORT="${PUSHER_PORT}"
VITE_PUSHER_SCHEME="${PUSHER_SCHEME}"
----------------------------------------------------------------------------
*/


/* resources/js/echo.js
----------------------------------------------------------------------------
import Echo from 'laravel-echo';

import Pusher from 'pusher-js';
window.Pusher = Pusher;

window.Echo = new Echo({
    broadcaster: "pusher",
    key: import.meta.env.VITE_PUSHER_APP_KEY,
    cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER,
    forceTLS: true,
    wsHost: import.meta.env.VITE_PUSHER_HOST,
    wsPort: import.meta.env.VITE_PUSHER_PORT,
    wssPort: import.meta.env.VITE_PUSHER_PORT,
    enabledTransports: ["ws", "wss"],
});

console.log("[Echo] ready?", !!window.Echo);
----------------------------------------------------------------------------
*/


/* cmd
----------------------------------------------------------------------------
composer require livewire/livewire
php artisan livewire:layout
----------------------------------------------------------------------------
*/


/* resources/views/components/layouts/app.blade.php
----------------------------------------------------------------------------
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ $title ?? 'Laravel websocket' }}</title>
        
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body>
        {{ $slot }}
    </body>
</html>
----------------------------------------------------------------------------
*/


/* cmd
----------------------------------------------------------------------------
php artisan livewire:make Counter/Index
----------------------------------------------------------------------------
*/


/* app/Livewire/Chat/Index.php
----------------------------------------------------------------------------
namespace App\Livewire\Chat;

use Livewire\Component;

class Index extends Component
{
    public function render()
    {
        return view('livewire.chat.index');
    }
}
----------------------------------------------------------------------------
*/


/* resources/views/livewire/chat/index.blade.php
----------------------------------------------------------------------------
<div></div>
----------------------------------------------------------------------------
*/


/* routes/web.php
----------------------------------------------------------------------------
Route::get('/', App\Livewire\Chat\Index::class);
----------------------------------------------------------------------------
*/


/* cmd
----------------------------------------------------------------------------
npm run dev
php artisan serve
----------------------------------------------------------------------------
*/


/* browser/console
----------------------------------------------------------------------------
[Echo] ready? true
----------------------------------------------------------------------------
*/


/* cmd
----------------------------------------------------------------------------
php artisan make:event Chat/ChatEvent
----------------------------------------------------------------------------
*/



/* app/Events/Chat/ChatEvent.php
----------------------------------------------------------------------------
namespace App\Events\Chat;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ChatEvent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public string $roomCode,
        public array $data
    )
    {
        //
    }

    public function broadcastOn(): array
    {
        return [
            new Channel("chat-room.{$this->roomCode}")
        ];
    }
    
    public function broadcastAs(): string
    {
        return 'chat';
    }
}
----------------------------------------------------------------------------
*/


/* cmd
----------------------------------------------------------------------------
php artisan make:controller Chat/ChatController
----------------------------------------------------------------------------
*/


/* app/Http/Controllers/Chat/ChatController.php
----------------------------------------------------------------------------
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
----------------------------------------------------------------------------
*/


/* resources/views/livewire/chat/index.blade.php
----------------------------------------------------------------------------
<div
    x-on:keydown.enter.prevent="$store.chatIndex.send()"
    x-init="
        Alpine.store('chatIndex', {
            loading: false,
            messages: [],
            message: '',
            roomCode: '12345',

            async send() {
                this.loading = true;
                
                if (this.message.trim() === '') {
                    this.loading = false;
                    return;
                }

                const response = await axios.post('{{ route('chat.post') }}', {
                    roomCode: this.roomCode,
                    message: this.message
                });
                
                this.loading = false;
                this.message = '';
            },
            
            async clear() {
                this.loading = true;

                const response = await axios.post('{{ route('chat.clear') }}', {
                    roomCode: this.roomCode
                });
                
                this.loading = false;
            }
        });

        roomCode = $store.chatIndex.roomCode;

        Echo.channel(`chat-room.${roomCode}`)
            .listen('.chat', (e) => {
                console.log('chat', e);
                $store.chatIndex.messages = e.data.messages;
            })
            .error(error => {
                console.log('error', error);
            });
        
        $store.chatIndex.send();
    "
    style="padding: 10px;"
>
    {{-- Input --}}
    <input x-model="$store.chatIndex.message" style="padding: 5px 10px; border: 1px solid #ccc; width: 200px;">

    {{-- Send --}}
    <button @click="$store.chatIndex.send()" :disabled="$store.chatIndex.loading" style="padding: 5px 10px; cursor: pointer; border: 1px solid #ccc;">
        <span x-show="$store.chatIndex.loading">Loading...</span>
        <span x-show="!$store.chatIndex.loading">Send</span>
    </button>

    {{-- Clear --}}
    <br>
    <button @click="$store.chatIndex.clear()" :disabled="$store.chatIndex.loading" style="padding: 5px 10px; cursor: pointer; border: 1px solid #ccc;">
        <span x-show="$store.chatIndex.loading">Loading...</span>
        <span x-show="!$store.chatIndex.loading">Clear</span>
    </button>

    {{-- Messages --}}
    <div style="margin-top: 10px;">
        <template x-for="(msg, index) in $store.chatIndex.messages" :key="msg + index">
            <div style="padding: 5px; border-bottom: 1px solid #eee;" x-text="msg"></div>
        </template>
    </div>
</div>

----------------------------------------------------------------------------
*/


/* routes/web.php
----------------------------------------------------------------------------
use Illuminate\Support\Facades\Route;

Route::get('/', App\Livewire\Chat\Index::class);
Route::post('/chat', [App\Http\Controllers\Chat\ChatController::class, 'chat'])->name('chat.post');
Route::post('/clear', [App\Http\Controllers\Chat\ChatController::class, 'clear'])->name('chat.clear');
----------------------------------------------------------------------------
*/


