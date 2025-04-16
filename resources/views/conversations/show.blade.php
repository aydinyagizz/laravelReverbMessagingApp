@php use Illuminate\Support\Facades\Auth; @endphp
@extends('layouts.app')

@section('content')
    <div class="container" id="app">

        @if (session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger">
                {{ session('error') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="row mb-3">
            <div class="col">
                <div class="d-flex justify-content-between align-items-center">
                    <h2>
                        @if($conversation->title)
                            {{ $conversation->title }}
                        @else
                            @php
                                $otherParticipants = $conversation->participants->where('user_id', '!=', Auth::id());
                                $title = $otherParticipants->pluck('user.name')->join(', ');
                            @endphp
                            {{ $title }}
                        @endif
                    </h2>
                    <a href="{{ route('conversations.index') }}" class="btn btn-secondary">Geri</a>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-9">
                <div class="chat-container" id="chatContainer">
                    @foreach($conversation->messages->sortBy('created_at') as $message)
                        <div
                            class="message {{ $message->sender_id == Auth::id() ? 'message-sent' : 'message-received' }}">
                            <div class="message-content">{{ $message->content }}</div>
                            <div class="message-info">
                                {{ $message->sender->name }} - {{ $message->created_at->format('d.m.Y H:i') }}
                            </div>
                        </div>
                        <div style="clear: both;"></div>
                    @endforeach
                </div>

                <form id="messageForm" action="{{ route('messages.store', $conversation) }}" method="POST">
                    @csrf
                    <div class="input-group mt-3">
                        <input type="text" id="content" name="content" class="form-control"
                               placeholder="Mesajınızı yazın..." required>
                        <button class="btn btn-primary" type="submit">Gönder</button>
                    </div>
                </form>
            </div>

            <div class="col-md-3">
                <div class="card">
                    <div class="card-header">Katılımcılar</div>
                    <div class="card-body">
                        <ul class="list-group list-group-flush">
                            @foreach($conversation->participants as $participant)
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    {{ $participant->user->name }}
                                    <span id="status-{{ $participant->user->id }}"
                                          class="badge bg-secondary">Çevrimdışı</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const chatContainer = document.getElementById('chatContainer');
            const messageForm = document.getElementById('messageForm');
            const contentInput = document.getElementById('content');

            // Sohbeti en aşağı kaydır
            function scrollToBottom() {
                chatContainer.scrollTop = chatContainer.scrollHeight;
            }

            // Sayfa yüklendiğinde sohbeti en aşağı kaydır
            scrollToBottom();

            // Mesaj gönderme işlemi
            messageForm.addEventListener('submit', function (e) {
                e.preventDefault();

                const formData = new FormData(messageForm);

                fetch(messageForm.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                })
                    .then(response => response.json())
                    .then(data => {
                        // Mesaj gönderildikten sonra input'u temizle
                        contentInput.value = '';

                        // Arayüze mesajı ekle
                        const messageDiv = document.createElement('div');
                        messageDiv.className = 'message message-sent';
                        messageDiv.innerHTML = `
                    <div class="message-content">${data.content}</div>
                    <div class="message-info">
                        ${data.sender.name} - ${new Date(data.created_at).toLocaleString('tr-TR')}
                    </div>
                `;
                        chatContainer.appendChild(messageDiv);

                        const clearDiv = document.createElement('div');
                        clearDiv.style.clear = 'both';
                        chatContainer.appendChild(clearDiv);

                        // Sohbeti en aşağı kaydır
                        scrollToBottom();
                    })
                    .catch(error => {
                        console.error('Error:', error);
                    });
            });

            // Okundu olarak işaretle
            fetch('{{ route("messages.read", $conversation) }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Content-Type': 'application/json'
                }
            });

            // Laravel Echo ile real-time mesajlaşma
            const conversationId = {{ $conversation->id }};
            const currentUserId = {{ Auth::id() }};

            window.Echo.join(`conversation.${conversationId}`)
                .here(users => {
                    console.log('Katılan kullanıcılar:', users); // ✅
                    users.forEach(user => {
                        const statusElement = document.getElementById(`status-${user.id}`);
                        if (statusElement) {
                            statusElement.className = 'badge bg-success';
                            statusElement.textContent = 'Çevrimiçi';
                        }
                    });
                })
                .joining(user => {
                    console.log('Sohbete katılan:', user); // ✅
                    const statusElement = document.getElementById(`status-${user.id}`);
                    if (statusElement) {
                        statusElement.className = 'badge bg-success';
                        statusElement.textContent = 'Çevrimiçi';
                    }
                })
                .leaving(user => {
                    console.log('Sohbetten ayrılan:', user); // ✅
                    const statusElement = document.getElementById(`status-${user.id}`);
                    if (statusElement) {
                        statusElement.className = 'badge bg-secondary';
                        statusElement.textContent = 'Çevrimdışı';
                    }
                })
                .listen('NewMessage', (e) => {
                    console.log('Yeni mesaj:', e);
                    if (e.sender_id !== currentUserId) {
                        // Yeni mesaj geldiğinde arayüze ekle
                        const messageDiv = document.createElement('div');
                        messageDiv.className = 'message message-received';
                        messageDiv.innerHTML = `
                        <div class="message-content">${e.content}</div>
                        <div class="message-info">
                            ${e.sender_name} - ${new Date(e.created_at).toLocaleString('tr-TR')}
                        </div>
                    `;
                        chatContainer.appendChild(messageDiv);

                        const clearDiv = document.createElement('div');
                        clearDiv.style.clear = 'both';
                        chatContainer.appendChild(clearDiv);

                        // Sohbeti en aşağı kaydır
                        scrollToBottom();

                        // Mesajı okundu olarak işaretle
                        fetch('{{ route("messages.read", $conversation) }}', {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                                'Content-Type': 'application/json'
                            }
                        });
                    }
                });
        });
    </script>
@endpush
