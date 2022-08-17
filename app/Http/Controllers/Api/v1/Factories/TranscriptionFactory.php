<?php

namespace App\Http\Controllers\Api\v1\Factories;

class TranscriptionFactory
{
    public static function getObject($provider)
    {
        $providerClass = "App\\Http\\Controllers\\Api\\v1\\TranscriptionProviders\\" . $provider . "Transcription";
        return new $providerClass();
    }
}
