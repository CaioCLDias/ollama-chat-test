<?php

namespace App\Http\Controllers\Common;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\ChatHistory;
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

    /**
     * @OA\Get(
     *     path="/api/chat/history",
     *     tags={"Chat"},
     *     summary="Get chat history of the authenticated user",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Chat history fetched successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Chat History fetched successfully."),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="user_id", type="integer", example=1),
     *                     @OA\Property(property="message", type="string", example="Hello, how are you?"),
     *                     @OA\Property(property="response", type="string", example="I'm fine, thank you!"),
     *                     @OA\Property(property="created_at", type="string", format="date-time", example="2025-06-05T00:00:00Z"),
     *                     @OA\Property(property="updated_at", type="string", format="date-time", example="2025-06-05T00:00:00Z")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Failed to fetch chat history",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Failed to fetch chat history."),
     *             @OA\Property(property="data", type="object", example=null)
     *         )
     *     )
     * )
     */
    public function getChatHistory(Request $request)
    {
        try {
             $userId = request()->user()->id;

            $messages = ChatHistory::where("user_id", $userId)
                ->orderBy('created_at', 'desc')
                ->orderBy('id', 'desc')
                ->get();

            return ApiResponse::success($messages, 'Chat History fetched successfully,');
        } catch (\Throwable $e) {
            return ApiResponse::error('Failed to fetch chat history,', 500, $e->getMessage());
        }
    }

    public function sendAsyncMessage(Request $request)
    {

       
        $message = $request->get('message');

        $stream = $this->chatService->sendAssyncMessage($message);

        return response()->stream(function () use ($stream) {
            while (!$stream->eof()){
                ob_flush();
                flush();
            }
        }, 200,[
        'Content-Type' => 'text/plain',
        'Cache-Control' => 'no-cache',
        'X-Accel-Buffering' => 'no',]
        );

    }
}
