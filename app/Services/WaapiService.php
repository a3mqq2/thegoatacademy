<?php
// app/Services/WaapiService.php
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
        $id          = "62050";
        $this->token = "JF1wtM5asTKUd5hV4PBLxPkbcdmOyVowWjCj5uNn6706ce55";
        $this->base  = "https://waapi.app/api/v1/instances/{$id}/client/action/";
    }

    private function call(string $path, array $data = []): array
    {
        $resp = Http::withToken($this->token)
                    ->acceptJson()
                    // ->asForm()   // ✅ المهم جداً
                    ->post($this->base . $path, $data);

        Log::info('WaAPI', ['path' => $path, 'status' => $resp->status(), 'body' => $resp->json()]);
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
            'url'     => $url,
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
