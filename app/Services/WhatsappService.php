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
        $this->instanceId = env('WAAPI_INSTANCE_ID');
        $this->token      = env('WAAPI_TOKEN');
        $this->base       = "https://waapi.app/api/v1/instances/{$this->instanceId}/client/action/";
    }

    private function call(string $path, array $data = []): array
    {
        $resp = Http::withToken($this->token)
                    ->acceptJson()->asJson()
                    ->post($this->base.$path, $data);

        Log::info('WaAPI', ['path'=>$path,'status'=>$resp->status(),'body'=>$resp->json()]);
        return $resp->json();
    }

    /* =========== عمليات متكرّرة =========== */
    public function sendText(string $chatId, string $text)
    {
        return $this->call('send-message', compact('chatId','text'));
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
