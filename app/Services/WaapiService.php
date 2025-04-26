<?php
namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WaapiService
{
    private string $instanceId;
    private string $token;
    private string $base;

    public function __construct()
    {
        $this->token = env('WAAPI_TOKEN');
        $this->base  = env('WAAPI_URL');
    }

    // app/Services/WaapiService.php
    private function call(string $path, array $data = []): array
    {
        $resp = Http::withToken($this->token)
            ->withBody(json_encode($data, JSON_UNESCAPED_UNICODE), 'application/json')
            ->acceptJson()                                // للحصول على JSON فى الاستجابة
            ->post($this->base.$path);                   // لا نمرر $data هنا

        Log::info('WaAPI', [
            'path'   => $path,
            'status' => $resp->status(),
            'body'   => $resp->json(),
        ]);

        return $resp->json();
    }

    public function sendText(string $chatId, string $text)
    {
        return $this->call('send-message', [
            'chatId'  => $chatId,
            'message' => $text,
        ]);
    }

    public function sendImage(string $chatId, string $url, string $caption = '')
    {
        return $this->call('send-media', [
            'chatId'  => $chatId,
            'mediaUrl'     => $url,
            'caption' => $caption,
            'type'    => 'image',
        ]);
    }

    public function createPoll(string $chatId, string $question, array $options)
    {
        return $this->call('create-poll', [
            'chatId'   => $chatId,
            'question' => $question,
            'options'  => $options,
        ]);
    }

    public function getAllChats()       { return $this->call('getAllChats'); }
    public function getGroupInfo($id)   { return $this->call('getGroupInfo', ['chatId'=>$id]); }
}
