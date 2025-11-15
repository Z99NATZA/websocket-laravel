<div
    x-on:keydown.enter.prevent="$store.chatIndex.send()"
    x-init="
        Alpine.store('chatIndex', {
            loading: false,
            messages: [],
            message: '',
            roomCode: '12345',
            first: true,

            async send() {
                this.loading = true;
                
                if (!this.first && this.message.trim() === '') {
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
