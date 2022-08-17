<?php

namespace App\Repositories\Interfaces;

interface TranscriptCaptionRepositoryInterface
{
    public function listTranscript($param);

    public function getTranscript($param);

    public function addTranscript($param);

    public function updateTranscript($param);

    public function deleteTranscript($param);
}
