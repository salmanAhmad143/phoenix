<?php

namespace Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class CaptionTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function testStore()
    {
        $this->withoutExceptionHandling();
        $auth = [
            'HTTP_Authorization' => 'Bearer ' . 'eJQ9935r68PRJzLEpF2xhkTCwykpgNhb4h799T4UjMc2KHx9oJw3pDKa4JAyO8MMtDAEMYlzRqR3WDYF',
        ];
        $data = [
            'mediaId' => "NLMmDVndR7",
            'languageId' => 29,
            'start' => 0,
            'end' => 1600,
            'text' => "If you only have 24 hours in  a day your success",
        ];
        $response = $this->json('POST', 'http://phoenix.localhost/api/v1/caption/create', $data, $auth);
        $response->assertStatus(200);
        $response->assertJson([
            "success" => true,
            "message" => config('constant.CAPTION_CREATE_MESSAGE')
        ]);
        $response->assertJsonStructure([
            "success",
            "message",
            "data" => ['mediaCaptionId']
        ]);
    }

    public function testUpdate()
    {
        $this->withoutExceptionHandling();
        $auth = [
            'HTTP_Authorization' => 'Bearer ' . 'eJQ9935r68PRJzLEpF2xhkTCwykpgNhb4h799T4UjMc2KHx9oJw3pDKa4JAyO8MMtDAEMYlzRqR3WDYF',
        ];
        $data = [
            'captions' => [
                [
                    'mediaCaptionId' => "NLMmDVndR7",
                    'start' => 1,
                    'end' => 1600,
                    'text' => "If you only have 24 hours in  a day your success",
                ],
                [
                    'mediaCaptionId' => "Jpyn5kmvYX",
                    'start' => 1700,
                    'end' => 12300,
                    'text' => "As u slegs 24 uur per dag het, is u sukses",
                ]
            ],
            'captionType' => 'translation'
        ];
        $response = $this->json('PUT', 'http://phoenix.localhost/api/v1/caption/update', $data, $auth);
        $response->assertStatus(200);
        $response->assertJson([
            "success" => true,
            "message" => config('constant.CAPTION_UPDATE_MESSAGE')
        ]);
    }

    public function testDelete()
    {
        $this->withoutExceptionHandling();
        $auth = [
            'HTTP_Authorization' => 'Bearer ' . 'eJQ9935r68PRJzLEpF2xhkTCwykpgNhb4h799T4UjMc2KHx9oJw3pDKa4JAyO8MMtDAEMYlzRqR3WDYF',
        ];
        $data = [
            'mediaCaptionId' => "BNQnyaLnjO",
        ];
        $response = $this->json('DELETE', 'http://phoenix.localhost/api/v1/caption/delete', $data, $auth);
        $response->assertStatus(200);
        $response->assertJson([
            "success" => true,
            "message" => config('constant.CAPTION_DELETE_MESSAGE')
        ]);
    }
}
