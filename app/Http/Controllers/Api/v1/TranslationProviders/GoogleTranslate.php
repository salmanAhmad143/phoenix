<?php

namespace App\Http\Controllers\Api\v1\TranslationProviders;

use Google\Cloud\Translate\V2\TranslateClient;
use App\Http\Controllers\Api\v1\Interfaces\Translation;

class GoogleTranslate implements Translation
{
    public function translate($param)
    {
        /**
         * SETUP URL: https://cloud.google.com/translate/docs/basic/setup-basic
         *
         * TODO: Need to register google application credentials to server
         * Windows - set GOOGLE_APPLICATION_CREDENTIALS=""
         * Linux - export GOOGLE_APPLICATION_CREDENTIALS=""
         */

        //TODO: update this for better option then saving key here.
        putenv('GOOGLE_APPLICATION_CREDENTIALS=' . env('GOOGLE_APPLICATION_CREDENTIALS'));
        $translate = new TranslateClient();
        return $translate->translate($param['sourceText'], [
            'target' => $param['targetLanguage'],
        ]);
    }
}
