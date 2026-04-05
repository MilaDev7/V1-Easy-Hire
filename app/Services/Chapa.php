<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class Chapa
{
    protected $secretKey;

    protected $baseUrl;

    public function __construct()
    {
        $this->secretKey = env('CHAPA_SECRET_KEY');
        $this->baseUrl = rtrim(env('CHAPA_BASE_URI', 'https://api.chapa.co/v1'), '/');
    }

    public static function generateReference(?string $transactionPrefix = null)
    {
        if ($transactionPrefix) {
            return $transactionPrefix.'_'.uniqid(time());
        }

        return env('APP_NAME', 'EasyHire').'_chapa_'.uniqid(time());
    }

    public function initializePayment(array $data)
    {
        $response = Http::withToken($this->secretKey)->post(
            $this->baseUrl.'/transaction/initialize',
            $data
        );

        if ($response->failed()) {
            Log::error('Chapa payment initialization failed', [
                'status' => $response->status(),
                'body' => $response->json() ?? $response->body(),
            ]);
        }

        return $response->json();
    }

    public function verifyTransaction($txRef)
    {
        $response = Http::withToken($this->secretKey)->get(
            $this->baseUrl.'/transaction/verify/'.$txRef
        );

        if ($response->failed()) {
            Log::error('Chapa transaction verification failed', [
                'status' => $response->status(),
                'body' => $response->json() ?? $response->body(),
                'tx_ref' => $txRef,
            ]);
        }

        return $response->json();
    }
}
