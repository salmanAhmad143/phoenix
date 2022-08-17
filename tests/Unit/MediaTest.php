<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Http\UploadedFile;

class MediaTest extends TestCase
{
    public function testStore()
    {
        $this->withoutExceptionHandling();
        $auth = [
            'HTTP_Authorization' => 'Bearer ' . 'eJQ9935r68PRJzLEpF2xhkTCwykpgNhb4h799T4UjMc2KHx9oJw3pDKa4JAyO8MMtDAEMYlzRqR3WDYF',
        ];
        $data = [
            'projectId' => "NLMmDVndR7",
            'mediaFiles' => ['media1', 'media2'],
        ];
        $response = $this->json('POST', 'http://phoenix.localhost/api/v1/media', $data, $auth);
        $response->assertStatus(200);
        $response->assertJson([
            "success" => true,
            "mediaIds" => []
        ]);
    }

    public function testIndex()
    {
        $this->withoutExceptionHandling();
        $auth = [
            'HTTP_Authorization' => 'Bearer ' . 'eJQ9935r68PRJzLEpF2xhkTCwykpgNhb4h799T4UjMc2KHx9oJw3pDKa4JAyO8MMtDAEMYlzRqR3WDYF',
        ];
        $data = [
            'projectId' => 'NLMmDVndR7',
        ];
        $response = $this->json('GET', 'http://phoenix.localhost/api/v1/projects/media', $data, $auth);
        $response->assertStatus(200);
        $response->assertJsonStructure([
            "success",
            "data" => ["media"]
        ]);
    }

    public function testShow()
    {
        $this->withoutExceptionHandling();
        $auth = [
            'HTTP_Authorization' => 'Bearer ' . 'eJQ9935r68PRJzLEpF2xhkTCwykpgNhb4h799T4UjMc2KHx9oJw3pDKa4JAyO8MMtDAEMYlzRqR3WDYF',
        ];
        $data = [
            'mediaId' => 'NLMmDVndR7',
        ];
        $response = $this->json('GET', 'http://phoenix.localhost/api/v1/media/details', $data, $auth);
        $response->assertStatus(200);
        $response->assertJsonStructure([
            "success",
            "data" => ["media"]
        ]);
    }

    public function testUpdate()
    {
        $this->withoutExceptionHandling();
        $auth = [
            'HTTP_Authorization' => 'Bearer ' . 'eJQ9935r68PRJzLEpF2xhkTCwykpgNhb4h799T4UjMc2KHx9oJw3pDKa4JAyO8MMtDAEMYlzRqR3WDYF',
        ];
        $data = [
            'mediaId' => "NLMmDVndR7",
            'name' => "Project1",
            'workflowId' => '',
            'languageId' => '',
        ];
        $response = $this->json('PUT', 'http://phoenix.localhost/api/v1/media/update', $data, $auth);
        $response->assertStatus(200);
        $response->assertJson([
            "success" => true,
            "message" => config('constant.MEDIA_UPDATE_MESSAGE')
        ]);
    }

    public function testDestroy()
    {
        $this->withoutExceptionHandling();
        $auth = [
            'HTTP_Authorization' => 'Bearer ' . 'eJQ9935r68PRJzLEpF2xhkTCwykpgNhb4h799T4UjMc2KHx9oJw3pDKa4JAyO8MMtDAEMYlzRqR3WDYF',
        ];
        $data = [
            'mediaId' => "NLMmDVndR7",
        ];
        $response = $this->json('DELETE', 'http://phoenix.localhost/api/v1/media/delete', $data, $auth);
        $response->assertStatus(200);
        $response->assertJson([
            "success" => true,
            "message" => config('constant.MEDIA_DELETE_MESSAGE')
        ]);
    }
}
