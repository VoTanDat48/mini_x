<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Inertia\Inertia;

class BotController extends Controller
{
    /**
     * Display the MiniX AI Bot chat page.
     */
    public function index(Request $request)
    {
        return Inertia::render('Bot/Index', [
            'messages' => $request->session()->get('bot_messages', []),
        ]);
    }

    /**
     * Send a message to the Gemini AI API and append response to conversation.
     */
    public function chat(Request $request)
    {
        $validated = $request->validate([
            'message' => ['required', 'string', 'max:1000'],
        ]);

        $messages = $request->session()->get('bot_messages', []);

        // 1. Append user's message
        $userMsg = [
            'id' => uniqid('msg_', true),
            'role' => 'user',
            'content' => trim($validated['message']),
            'time' => now()->format('H:i'),
        ];
        $messages[] = $userMsg;

        // 2. Prepare payload for Gemini (last 10 messages for context)
        $historyContents = [];
        foreach (array_slice($messages, -10) as $msg) {
            $historyContents[] = [
                'role' => $msg['role'] === 'user' ? 'user' : 'model',
                'parts' => [
                    ['text' => $msg['content']],
                ],
            ];
        }

        $apiKey = config('services.gemini.key');
        $model = config('services.gemini.model', 'gemini-3.5-flash-lite');

        $aiReply = '';

        if (empty($apiKey)) {
            $aiReply = 'Hệ thống chưa được cấu hình GEMINI_API_KEY. Vui lòng thêm khóa API vào file .env.';
        } else {
            try {
                $endpoint = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}";

                $response = Http::withoutVerifying()
                    ->timeout(25)
                    ->post($endpoint, [
                        'systemInstruction' => [
                            'parts' => [
                                [
                                    'text' => 'Bạn là MiniX AI - Trợ lý trí tuệ nhân tạo thông minh được tích hợp trực tiếp vào mạng xã hội MiniX. Bạn luôn trả lời thân thiện, lịch thiệp, thông minh, ngắn gọn và hữu ích bằng Tiếng Việt (hoặc ngôn ngữ người dùng nhập). Bạn có thể giải đáp kiến thức tổng quát, viết tweet, gợi ý ý tưởng truyền thông và hỗ trợ lập trình.'
                                ]
                            ]
                        ],
                        'contents' => $historyContents,
                    ]);

                if ($response->successful()) {
                    $json = $response->json();
                    $aiReply = $json['candidates'][0]['content']['parts'][0]['text'] ?? 'Xin lỗi, tôi chưa thể trả lời câu hỏi này lúc này.';
                } else {
                    $aiReply = 'Hiện tại dịch vụ AI đang bận hoặc gặp sự cố kết nối (Mã lỗi: ' . $response->status() . '). Bạn vui lòng thử lại sau giây lát!';
                }
            } catch (\Throwable $e) {
                $aiReply = 'Không thể kết nối đến máy chủ AI (Lỗi kết nối mạng). Vui lòng thử lại sau.';
            }
        }

        // 3. Append bot's message
        $botMsg = [
            'id' => uniqid('msg_', true),
            'role' => 'assistant',
            'content' => trim($aiReply),
            'time' => now()->format('H:i'),
        ];
        $messages[] = $botMsg;

        $request->session()->put('bot_messages', $messages);

        return back();
    }

    /**
     * Clear all messages in current session.
     */
    public function clear(Request $request)
    {
        $request->session()->forget('bot_messages');

        return back();
    }
}

