<?php


namespace App\Http\Controllers\Api\v1\Factories;


class UploadFactory
{
    public static function getObject($provider)
    {
        $providerClass = "App\\Http\\Controllers\\Api\\v1\\UploadProviders\\" . $provider . "Storage";
        return new $providerClass();
    }
}
