<?php

namespace App\Http\Controllers\Api\v1\Interfaces;

interface Transcription
{
    public function pushMedia($param);

    public function startTranscription($param);
    
    public function getTranscription($param);
}
