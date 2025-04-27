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

    private function call(string $path, array $data = []): array
    {
        $resp = Http::withToken($this->token)
            ->withBody(json_encode($data, JSON_UNESCAPED_UNICODE), 'application/json')
            ->acceptJson()
            ->post($this->base.$path);

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
            'chatId'    => $chatId,
            'mediaUrl'  => $url,
            'caption'   => $caption,
            'type'      => 'image',
        ]);
    }

    public function sendBase64Image(string $chatId, string $base64Image, string $mimeType = 'image/jpeg', string $caption = '')
    {
        return $this->call('send-media', [
            'chatId'       => $chatId,
            'type'         => 'image',
            'media'        => 'data:'.$mimeType.';base64,'.$base64Image,
            'caption'      => $caption,
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
