<?php

namespace App\Http\Controllers\Common;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Services\ChatService;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    public function __construct(protected ChatService $chatService){}
  

    public function store(Request $request)
    {
        $request->validate([
            'message' => 'required|string',
        ]);

        $userId = request()->user()->id;
        $message = $request->input('message');

        try {
            $chat = $this->chatService->sendMessage($userId, $message);
            return ApiResponse::success($chat, 'Chat message sent successfully');
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to send chat message', 500, $e->getMessage());
        }
    }
}
