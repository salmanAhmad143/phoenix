<?php

namespace Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class GuidelineTest extends TestCase
{
    public function testIndex()
    {
        $this->withoutExceptionHandling();
        $auth = [
            'HTTP_Authorization' => 'Bearer ' . 'eJQ9935r68PRJzLEpF2xhkTCwykpgNhb4h799T4UjMc2KHx9oJw3pDKa4JAyO8MMtDAEMYlzRqR3WDYF',
        ];
        $data = [
            'name' => "Amazon",
            'languageId' => 1,
        ];
        $response = $this->json('GET', 'http://phoenix.localhost/api/v1/guideline', $data, $auth);
        $response->assertStatus(200);
        $response->assertJsonStructure([
            "success",
            "data" => ["guidelines"]
        ]);
    }

    public function testStore()
    {
        $this->withoutExceptionHandling();
        $auth = [
            'HTTP_Authorization' => 'Bearer ' . 'eJQ9935r68PRJzLEpF2xhkTCwykpgNhb4h799T4UjMc2KHx9oJw3pDKa4JAyO8MMtDAEMYlzRqR3WDYF',
        ];
        $data = [
            'name' => "Amazon",
            'minDuration' => 100,
            'maxDuration' => 150,
            'frameGap' => 2,
            'maxLinePerSubtitle' => 2,
            'maxCharsPerLine' => 30,
            'maxCharsPerSecond' => 10,
            'subtitleSyncAccuracy' => 10,
            'languageId' => 1,
        ];
        $response = $this->json('POST', 'http://phoenix.localhost/api/v1/guideline/create', $data, $auth);
        $response->assertStatus(200);
        $response->assertJson([
            "success" => true,
            "message" => config('constant.GUIDELINE_CREATE_MESSAGE')
        ]);
    }

    public function testUpdate()
    {
        $this->withoutExceptionHandling();
        $auth = [
            'HTTP_Authorization' => 'Bearer ' . 'eJQ9935r68PRJzLEpF2xhkTCwykpgNhb4h799T4UjMc2KHx9oJw3pDKa4JAyO8MMtDAEMYlzRqR3WDYF',
        ];
        $data = [
            'guidelineId' => "NLMmDVndR7",
            'name' => "Yahoo",
            'minDuration' => 100,
            'maxDuration' => 150,
            'frameGap' => 2,
            'maxLinePerSubtitle' => 2,
            'maxCharsPerLine' => 30,
            'maxCharsPerSecond' => 10,
            'subtitleSyncAccuracy' => 10,
            'languageId' => 1,
        ];
        $response = $this->json('PUT', 'http://phoenix.localhost/api/v1/guideline/update', $data, $auth);
        $response->assertStatus(200);
        $response->assertJson([
            "success" => true,
            "message" => config('constant.GUIDELINE_UPDATE_MESSAGE')
        ]);
    }

    public function testDestroy()
    {
        $this->withoutExceptionHandling();
        $auth = [
            'HTTP_Authorization' => 'Bearer ' . 'eJQ9935r68PRJzLEpF2xhkTCwykpgNhb4h799T4UjMc2KHx9oJw3pDKa4JAyO8MMtDAEMYlzRqR3WDYF',
        ];
        $data = [
            'guidelineId' => "NLMmDVndR7",
        ];
        $response = $this->json('DELETE', 'http://phoenix.localhost/api/v1/guideline/delete', $data, $auth);
        $response->assertStatus(200);
        $response->assertJson([
            "success" => true,
            "message" => config('constant.GUIDELINE_DELETE_MESSAGE')
        ]);
    }
}
