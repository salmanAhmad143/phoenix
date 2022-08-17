<?php

namespace App\Http\Controllers\Api\v1\Factories;

class TranslationFactory
{
    public static function getObject($provider)
    {
        $providerClass = "App\\Http\\Controllers\\Api\\v1\\TranslationProviders\\" . $provider . "Translate";
        return new $providerClass();
    }
}
