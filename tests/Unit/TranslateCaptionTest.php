<?php

namespace Tests\Unit;

use Tests\TestCase;

class TranslateCaptionTest extends TestCase
{
    public function testGenearteManualTranslation()
    {
        $this->genearteTranslation(false);
    }

    public function testGenearteAutoTranslation()
    {
        $this->genearteTranslation(true);
    }

    public function genearteTranslation($auto)
    {
        $this->withoutExceptionHandling();
        $auth = [
            'HTTP_Authorization' => 'Bearer ' . 'eJQ9935r68PRJzLEpF2xhkTCwykpgNhb4h799T4UjMc2KHx9oJw3pDKa4JAyO8MMtDAEMYlzRqR3WDYF',
        ];
        $data = [
            'mediaId' => "NLMmDVndR7",
            'languageId' => [128, 134],
            'auto' => $auto,
            'minDuration' => 100,
            'maxDuration' => 150,
            'frameGap' => 2,
            'maxLinePerSubtitle' => 2,
            'maxCharsPerLine' => 30,
            'maxCharsPerSecond' => 10,
            'subtitleSyncAccuracy' => 10,
        ];
        $response = $this->json('POST', 'http://phoenix.localhost/api/v1/media/captions/translation/create', $data, $auth);
        $response->assertStatus(200);
        $response->assertJson([
            "success" => true,
            "message" => config('constant.TRANSLATION_LIST_GENERATE_MESSAGE')
        ]);
    }

    public function testTranslationList()
    {
        $this->withoutExceptionHandling();
        $auth = [
            'HTTP_Authorization' => 'Bearer ' . 'eJQ9935r68PRJzLEpF2xhkTCwykpgNhb4h799T4UjMc2KHx9oJw3pDKa4JAyO8MMtDAEMYlzRqR3WDYF',
        ];
        $data = [
            'mediaId' => "NLMmDVndR7",
        ];
        $response = $this->json('GET', 'http://phoenix.localhost/api/v1/media/captions/translation/list', $data, $auth);
        $response->assertStatus(200);
        $response->assertJsonStructure([
            "success",
            "data" => ["list"]
        ]);
    }

    public function testTranslation()
    {
        $this->withoutExceptionHandling();
        $auth = [
            'HTTP_Authorization' => 'Bearer ' . 'eJQ9935r68PRJzLEpF2xhkTCwykpgNhb4h799T4UjMc2KHx9oJw3pDKa4JAyO8MMtDAEMYlzRqR3WDYF',
        ];
        $data = [
            'mediaId' => "NLMmDVndR7",
        ];
        $response = $this->json('GET', 'http://phoenix.localhost/api/v1/media/captions/translation/', $data, $auth);
        $response->assertStatus(200);
        $response->assertJsonStructure([
            "success",
            "data" => ["captions"]
        ]);
    }

    public function testTranslationListUpdate()
    {
        $this->withoutExceptionHandling();
        $auth = [
            'HTTP_Authorization' => 'Bearer ' . 'eJQ9935r68PRJzLEpF2xhkTCwykpgNhb4h799T4UjMc2KHx9oJw3pDKa4JAyO8MMtDAEMYlzRqR3WDYF',
        ];
        $data = [
            'mediaId' => "NLMmDVndR7",
            'languageId' => 129,
            'minDuration' => 100,
            'maxDuration' => 150,
            'frameGap' => 2,
            'maxLinePerSubtitle' => 2,
            'maxCharsPerLine' => 40,
            'maxCharsPerSecond' => 10,
            'subtitleSyncAccuracy' => 10,
        ];
        $response = $this->json('PUT', 'http://phoenix.localhost/api/v1/media/captions/translation/update', $data, $auth);
        $response->assertStatus(200);
        $response->assertJson([
            "success" => true,
        ]);
        $response->assertJsonStructure([
            "success",
            "data"
        ]);
    }

    public function testTranslationListDelete()
    {
        $this->withoutExceptionHandling();
        $auth = [
            'HTTP_Authorization' => 'Bearer ' . 'eJQ9935r68PRJzLEpF2xhkTCwykpgNhb4h799T4UjMc2KHx9oJw3pDKa4JAyO8MMtDAEMYlzRqR3WDYF',
        ];
        $data = [
            'mediaId' => "NLMmDVndR7",
            'languageId' => 2,
        ];
        $response = $this->json('DELETE', 'http://phoenix.localhost/api/v1/media/captions/translation/delete', $data, $auth);
        $response->assertStatus(200);
        $response->assertJson([
            "success" => true,
            "message" => config('constant.TRANSCRIPT_LIST_DELETE_MESSAGE')
        ]);
    }
}
