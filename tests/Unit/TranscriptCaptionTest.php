<?php

namespace Tests\Unit;

use Tests\TestCase;

class TranscriptCaptionTest extends TestCase
{
    public function testGenearteManualTranscription()
    {
        $this->genearteTranscription(false);
    }

    public function testGenearteAutoTranscription()
    {
        $this->genearteTranscription(true);
    }

    public function genearteTranscription($auto)
    {
        $this->withoutExceptionHandling();
        $auth = [
            'HTTP_Authorization' => 'Bearer ' . 'eJQ9935r68PRJzLEpF2xhkTCwykpgNhb4h799T4UjMc2KHx9oJw3pDKa4JAyO8MMtDAEMYlzRqR3WDYF',
        ];
        $data = [
            'mediaId' => "NLMmDVndR7",
            'auto' => $auto,
            'minDuration' => 100,
            'maxDuration' => 150,
            'frameGap' => 2,
            'maxLinePerSubtitle' => 2,
            'maxCharsPerLine' => 30,
            'maxCharsPerSecond' => 10,
            'subtitleSyncAccuracy' => 10,
        ];
        $response = $this->json('POST', 'http://phoenix.localhost/api/v1/media/captions/original/create', $data, $auth);
        $response->assertStatus(200);
        $response->assertJson([
            "success" => true,
            "message" => config('constant.TRANSCRIPT_LIST_GENERATE_MESSAGE')
        ]);
    }

    public function testTranscriptionList()
    {
        $this->withoutExceptionHandling();
        $auth = [
            'HTTP_Authorization' => 'Bearer ' . 'eJQ9935r68PRJzLEpF2xhkTCwykpgNhb4h799T4UjMc2KHx9oJw3pDKa4JAyO8MMtDAEMYlzRqR3WDYF',
        ];
        $data = [
            'mediaId' => "NLMmDVndR7",
        ];
        $response = $this->json('GET', 'http://phoenix.localhost/api/v1/media/captions/original/list', $data, $auth);
        $response->assertStatus(200);
        $response->assertJsonStructure([
            "success",
            "data" => ["list"]
        ]);
    }

    public function testCaptions()
    {
        $this->withoutExceptionHandling();
        $auth = [
            'HTTP_Authorization' => 'Bearer ' . 'eJQ9935r68PRJzLEpF2xhkTCwykpgNhb4h799T4UjMc2KHx9oJw3pDKa4JAyO8MMtDAEMYlzRqR3WDYF',
        ];
        $data = [
            'mediaId' => "NLMmDVndR7",
        ];
        $response = $this->json('GET', 'http://phoenix.localhost/api/v1/media/captions/original/', $data, $auth);
        $response->assertStatus(200);
        $response->assertJsonStructure([
            "success",
            "data" => ["captions"]
        ]);
    }

    public function testTranscriptionListUpdate()
    {
        $this->withoutExceptionHandling();
        $auth = [
            'HTTP_Authorization' => 'Bearer ' . 'eJQ9935r68PRJzLEpF2xhkTCwykpgNhb4h799T4UjMc2KHx9oJw3pDKa4JAyO8MMtDAEMYlzRqR3WDYF',
        ];
        $data = [
            'mediaId' => "NLMmDVndR7",
            'minDuration' => 100,
            'maxDuration' => 150,
            'frameGap' => 2,
            'maxLinePerSubtitle' => 2,
            'maxCharsPerLine' => 40,
            'maxCharsPerSecond' => 10,
            'subtitleSyncAccuracy' => 10,
        ];
        $response = $this->json('PUT', 'http://phoenix.localhost/api/v1/media/captions/original/update', $data, $auth);
        $response->assertStatus(200);
        $response->assertJson([
            "success" => true,
        ]);
        $response->assertJsonStructure([
            "success",
            "data"
        ]);
    }

    public function testTranscriptionListDelete()
    {
        $this->withoutExceptionHandling();
        $auth = [
            'HTTP_Authorization' => 'Bearer ' . 'eJQ9935r68PRJzLEpF2xhkTCwykpgNhb4h799T4UjMc2KHx9oJw3pDKa4JAyO8MMtDAEMYlzRqR3WDYF',
        ];
        $data = [
            'mediaId' => "NLMmDVndR7",
        ];
        $response = $this->json('DELETE', 'http://phoenix.localhost/api/v1/media/captions/original/delete', $data, $auth);
        $response->assertStatus(200);
        $response->assertJson([
            "success" => true,
            "message" => config('constant.TRANSCRIPT_LIST_DELETE_MESSAGE')
        ]);
    }
}
