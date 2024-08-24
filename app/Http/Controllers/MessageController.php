<?php

namespace App\Http\Controllers;

use App\Events\SocketMessage;
use App\Http\Requests\StoreMessageRequest;
use App\Http\Resources\MessageResource;
use App\Models\Conversation;
use App\Models\Group;
use App\Models\Message;
use App\Models\Message_attachement;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class MessageController extends Controller
{
    use SoftDeletes;

    public function byUser(User $user)
    {
        $messages = Message::where('sender_id', auth()->id())
            ->where('reciever_id', $user->id)->orWhere('sender_id', $user->id)
            ->where('reciever_id', auth()->id())
            ->latest()->paginate(10);
        return inertia('Home', [
            'selectedConversation' => $user->toConversationArray(), 'messages' => MessageResource::collection($messages),
        ]);
    }

    public function byGroup(Group $group)
    {
        $messages = Message::where('group_id', $group->id)
            ->latest()
            ->paginate(10);

        return inertia('Home', [
            'selectedConversation' => $group->toConversationArray(),
            'messages' => MessageResource::collection($messages),
        ]);
    }

    public function loadOlder(Message $message)
    {
        if ($message->group_id) {
            $messages = Message::where('created_at', '<', $message->created_at)
                ->where('group_id', $message->group_id)
                ->latest()
                ->paginate(10);
        } else {
            $messages = Message::where('created_at', '<', $message->created_at)
                ->where(function ($query) use ($message) {
                    $query->where('sender_id', $message->sender_id)
                        ->where('reciever_id', $message->reciever_id)
                        ->orWhere('sender_id', $message->reciever_id)
                        ->where('reciever_id', $message->sender_id);
                })
                ->latest()
                ->paginate(10);
        }
        // Return the messages as a resource
        return MessageResource::collection($messages);
    }

    public function store(StoreMessageRequest $request)
    {
        $data = $request->validated();
        $data['sender_id'] = auth()->id();

        $receiverId = $data['reciever_id'] ?? null;
        $groupId = $data['group_id'] ?? null;
        $files = $request->file('attachments') ?? [];

        // Handle the message creation
        $message = Message::create($data);

        $attachments = [];
        if (!empty($files)) {
            foreach ($files as $file) {
                $directory = 'attachments/' . Str::random(32);
                Storage::makeDirectory($directory);

                $attachment = [
                    'message_id' => $message->id,
                    'name' => $file->getClientOriginalName(),
                    'mime' => $file->getClientMimeType(),
                    'size' => $file->getSize(),
                    'path' => $file->store($directory, 'public')
                ];

                $attachments[] = Message_attachement::create($attachment);
            }
        }

        if ($receiverId) {
            Conversation::updateConversationWithMessage($receiverId, auth()->id(), $message);
        }

        if ($groupId) {
            Group::updateGroupWithMessage($groupId, $message);
        }

        SocketMessage::dispatch($message);

        return new MessageResource($message);
    }


    public function destroy(Message $message)
    {
        if ($message->sender_id !== auth()->id()) {
            return response()->json(['message' => 'Forbidden'], 403);
        }
        $message->delete();
        return response('', 204);
    }
}
