<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\Participant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ConversationController extends Controller
{
    public function index()
    {
        $conversations = Auth::user()->conversations()
            ->with(['participants.user', 'messages' => function ($query) {
                $query->latest()->take(1);
            }])
            ->get();

        return view('conversations.index', compact('conversations'));
    }

    public function create()
    {
        $users = User::where('id', '!=', Auth::id())->get();
        return view('conversations.create', compact('users'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'nullable|string|max:255',
            'participants' => 'required|array',
            'participants.*' => 'exists:users,id'
        ]);

        $conversation = Conversation::create([
            'title' => $request->title ?: null,
        ]);

        // Gönderen kullanıcıyı da katılımcılara ekle
        $participants = array_unique(array_merge($request->participants, [Auth::id()]));

        foreach ($participants as $userId) {
            Participant::create([
                'conversation_id' => $conversation->id,
                'user_id' => $userId,
            ]);
        }

        return redirect()->route('conversations.show', $conversation);
    }

    public function show(Conversation $conversation)
    {
        // Kullanıcının bu konuşmanın bir parçası olup olmadığını kontrol et
        if (!$conversation->participants()->where('user_id', Auth::id())->exists()) {
            abort(403);
        }

        $conversation->load(['messages.sender', 'participants.user']);

        // Son okunma zamanını güncelle
        $participant = $conversation->participants()->where('user_id', Auth::id())->first();
        $participant->update(['last_read' => now()]);

        return view('conversations.show', compact('conversation'));
    }

    public function destroy(Conversation $conversation)
    {
        // Kullanıcının bu konuşmanın bir parçası olup olmadığını kontrol et
        if (!$conversation->participants()->where('user_id', Auth::id())->exists()) {
            abort(403);
        }

        $conversation->delete();

        return redirect()->route('conversations.index')->with('success', 'Konuşma silindi.');
    }
}
