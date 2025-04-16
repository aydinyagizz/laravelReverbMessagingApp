<?php

namespace App\Http\Controllers;

use App\Events\NewMessage;
use App\Models\Conversation;
use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MessageController extends Controller
{
    public function store(Request $request, Conversation $conversation)
    {
        // Kullanıcının bu konuşmanın bir parçası olup olmadığını kontrol et
        if (!$conversation->participants()->where('user_id', Auth::id())->exists()) {
            abort(403);
        }

        $request->validate([
            'content' => 'required|string',
        ]);

        $message = Message::create([
            'conversation_id' => $conversation->id,
            'sender_id' => Auth::id(),
            'content' => $request->input('content'),
        ]);

        // Son mesaj zamanını güncelle
        $conversation->touch();

        // Katılımcının son okuma zamanını güncelle
        $conversation->participants()->where('user_id', Auth::id())
            ->update(['last_read' => now()]);

        $message->load('sender');
        broadcast(new NewMessage($message))->toOthers();


        if ($request->ajax()) {
            return response()->json($message);
        }

        return back();
    }

    public function markAsRead(Conversation $conversation)
    {
        $participant = $conversation->participants()->where('user_id', Auth::id())->first();

        if ($participant) {
            $participant->update(['last_read' => now()]);
        }

        return response()->json(['success' => true]);
    }
}
