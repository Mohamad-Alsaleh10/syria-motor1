<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Message;
use App\Models\User; // Make sure to import the User model
use Illuminate\Validation\Rule;

class MessageController extends Controller
{
    /**
     * Display a listing of the messages.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        // Fetch messages with their sender and receiver
        $messages = Message::with(['sender', 'receiver'])->paginate(10);
        return view('admin.messages.index', compact('messages'));
    }

    /**
     * Show the form for creating a new message.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        // Get all users to select a receiver
        $users = User::all();
        return view('admin.messages.create', compact('users'));
    }

    /**
     * Store a newly created message in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $request->validate([
            'sender_id' => 'required|exists:users,id', // Admin sending the message
            'receiver_id' => 'required|exists:users,id',
            'subject' => 'required|string|max:255',
            'content' => 'required|string',
            'read_at' => 'nullable|date',
        ]);

        Message::create($request->all());

        return redirect()->route('admin.messages.index')->with('success', 'Message sent successfully.');
    }

    /**
     * Display the specified message.
     *
     * @param  \App\Models\Message  $message
     * @return \Illuminate\View\View
     */
    public function show(Message $message)
    {
        // Mark message as read if it's not already
        if (is_null($message->read_at)) {
            $message->update(['read_at' => now()]);
        }
        $message->load('sender', 'receiver');
        return view('admin.messages.show', compact('message'));
    }

    /**
     * Show the form for editing the specified message.
     * Note: Messages are typically not "edited" but rather "replied to".
     * This method can be used to change its status (e.g., mark as unread/read)
     * or add internal notes. For simplicity, we'll allow basic updates.
     *
     * @param  \App\Models\Message  $message
     * @return \Illuminate\View\View
     */
    public function edit(Message $message)
    {
        // For simplicity, we'll just show the message and allow marking as read/unread
        // or adding internal notes. Realistically, messages are rarely edited.
        return view('admin.messages.edit', compact('message'));
    }

    /**
     * Update the specified message in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Message  $message
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, Message $message)
    {
        $request->validate([
            'subject' => 'required|string|max:255',
            'body' => 'required|string',
            'read_at' => 'nullable|date', // Allow setting read_at
        ]);

        $message->update($request->all());

        return redirect()->route('admin.messages.index')->with('success', 'Message updated successfully.');
    }

    /**
     * Remove the specified message from storage.
     *
     * @param  \App\Models\Message  $message
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Message $message)
    {
        $message->delete();
        return redirect()->route('admin.messages.index')->with('success', 'Message deleted successfully.');
    }
}
