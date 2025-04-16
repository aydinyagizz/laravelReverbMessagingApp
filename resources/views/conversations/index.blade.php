@php use Illuminate\Support\Facades\Auth; @endphp
@extends('layouts.app')

@section('content')
    <div class="container">


        <script>
            setTimeout(function () {
                var messages = document.querySelectorAll('.messages');
                messages.forEach(function (message) {
                    message.style.display = 'none';
                });
            }, 5000);
        </script>
        @if (session('success'))
            <div class="alert alert-success messages">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger messages">
                {{ session('error') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger messages">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif


        <div class="row mb-4">
            <div class="col-md-12">
                <div class="d-flex justify-content-between align-items-center">
                    <h2>Konuşmalar</h2>
                    <a href="{{ route('conversations.create') }}" class="btn btn-primary">Yeni Konuşma</a>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                @if($conversations->isEmpty())
                    <div class="alert alert-info">
                        Henüz hiç konuşmanız bulunmuyor. Yeni bir konuşma başlatmak için "Yeni Konuşma" butonuna
                        tıklayın.
                    </div>
                @else
                    <div class="list-group">
                        @foreach($conversations as $conversation)
                            @php
                                $lastMessage = $conversation->messages->first();
                                $title = $conversation->title;
                                if (!$title) {
                                    $otherParticipants = $conversation->participants->where('user_id', '!=', Auth::id());
                                    $title = $otherParticipants->pluck('user.name')->join(', ');
                                }
                                $userParticipant = $conversation->participants->where('user_id', Auth::id())->first();
                                $unreadCount = $conversation->messages()
                                    ->where('created_at', '>', $userParticipant->last_read ?? now()->subYears(10))
                                    ->where('sender_id', '!=', Auth::id())
                                    ->count();
                            @endphp
                            <a href="{{ route('conversations.show', $conversation) }}"
                               class="list-group-item list-group-item-action">
                                <div class="d-flex w-100 justify-content-between">
                                    <h5 class="mb-1">{{ $title }}</h5>
                                    <small>{{ $lastMessage ? $lastMessage->created_at->diffForHumans() : 'Yeni' }}</small>
                                </div>
                                <div class="d-flex justify-content-between align-items-center">
                                    <p class="mb-1 text-truncate">
                                        @if($lastMessage)
                                            <strong>{{ $lastMessage->sender->name }}: </strong>
                                            {{ Str::limit($lastMessage->content, 50) }}
                                        @else
                                            Henüz mesaj yok
                                        @endif
                                    </p>
                                    <div class="d-flex align-items-center">
                                        @if($unreadCount > 0)
                                            <span class="badge rounded-pill bg-success me-2">{{ $unreadCount }}</span>
                                        @endif

                                        {{-- Silme Butonu --}}
                                        <form action="{{ route('conversations.destroy', $conversation) }}" method="POST"
                                              onsubmit="return confirm('Bu konuşmayı silmek istediğinize emin misiniz?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger">
                                                Sil
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </a>

                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
