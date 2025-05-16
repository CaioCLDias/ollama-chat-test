<?php

namespace App\Http\Controllers\Common;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Services\ChatService;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    public function __construct(protected ChatService $chatService) {}


    /**
     * @OA\Post(
     *     path="/api/chat",
     *     tags={"Chat"},
     *     summary="Send a message to the chat LLM",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"message"},
     *             @OA\Property(property="message", type="string", example="Hello, how are you?")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Chat message sent successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Chat message sent successfully"),
     *             @OA\Property(property="data", type="object", example={"message": "Hello, how are you?", "response": "I'm fine, thank you!"})
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Failed to send chat message"
     *     )
     * )
     */
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
