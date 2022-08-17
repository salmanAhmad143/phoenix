<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class TranslationFileTest extends TestCase
{
    use RefreshDatabase;

    public function testRegistration()
    {
        $data = [
            'email' => "pawan.umrao@lingualconsultancy.com",
            'api_token' => '417e16fc9244d3a02964842f4741cf73ed07e777c0694055f4d7b06035adeafb'
        ];
        $response = $this->json('POST', 'http://phoenix.localhost/api/v1/users/registration', $data);
        $response->assertStatus(200);
        $response->assertJsonStructure([
            "success",
            "message",
            "data" => ['emailVerificationCode']
        ]);
    }

    public function testCreateProject()
    {
        $this->withoutExceptionHandling();
        $auth = [
            'HTTP_Authorization' => 'Bearer ' . 'eJQ9935r68PRJzLEpF2xhkTCwykpgNhb4h799T4UjMc2KHx9oJw3pDKa4JAyO8MMtDAEMYlzRqR3WDYF',
        ];
        $data = [
            'name' => "Project1",
            'workflowId' => '',
        ];
        $response = $this->json('POST', 'http://phoenix.localhost/api/v1/projects/create', $data, $auth);
        $response->assertStatus(200);
        $response->assertJson([
            "success" => true,
            "message" => config('constant.PROJECT_CREATE_MESSAGE')
        ]);
    }

    public function testAllocateUser()
    {
        $this->withoutExceptionHandling();
        $auth = [
            'HTTP_Authorization' => 'Bearer ' . 'eJQ9935r68PRJzLEpF2xhkTCwykpgNhb4h799T4UjMc2KHx9oJw3pDKa4JAyO8MMtDAEMYlzRqR3WDYF',
        ];
        $data = [
            'projectId' => "NLMmDVndR7",
            'userId' => ['NLMmDVndR7'],
        ];
        $response = $this->json('POST', 'http://phoenix.localhost/api/v1/project/user/add', $data, $auth);
        $response->assertStatus(200);
        $response->assertJson([
            "success" => true,
            "message" => config('constant.PROJECT_USER_ADD_MESSAGE')
        ]);
    }

    public function testUploadMedia()
    {
        $this->withoutExceptionHandling();
        $auth = [
            'HTTP_Authorization' => 'Bearer ' . 'eJQ9935r68PRJzLEpF2xhkTCwykpgNhb4h799T4UjMc2KHx9oJw3pDKa4JAyO8MMtDAEMYlzRqR3WDYF',
        ];
        $data = [
            'projectId' => "NLMmDVndR7",
            'mediaFiles' => [UploadedFile::fake()->create('1.07.mp4')],
        ];
        $response = $this->json('POST', 'http://phoenix.localhost/api/v1/media', $data, $auth);
        $response->assertStatus(200);
        $response->assertJson([
            "success" => true,
            "message" => config('constant.MEDIA_UPLOAD_MESSAGE')
        ]);
    }

    public function testUpdateMedia()
    {
        $this->withoutExceptionHandling();
        $auth = [
            'HTTP_Authorization' => 'Bearer ' . 'eJQ9935r68PRJzLEpF2xhkTCwykpgNhb4h799T4UjMc2KHx9oJw3pDKa4JAyO8MMtDAEMYlzRqR3WDYF',
        ];
        $data = [
            'mediaId' => "NLMmDVndR7",
            'languageId' => 1,
        ];
        $response = $this->json('PUT', 'http://phoenix.localhost/api/v1/media/update', $data, $auth);
        $response->assertStatus(200);
        $response->assertJson([
            "success" => true,
            "message" => config('constant.MEDIA_UPDATE_MESSAGE')
        ]);
    }

    public function testGenearteManualTranscription()
    {
        $this->genearteTranscription(false);
    }

    // public function testGenearteAutoTranscription()
    // {
    //     $this->genearteTranscription(true);
    // }

    public function genearteTranscription($auto)
    {
        $this->withoutExceptionHandling();
        $auth = [
            'HTTP_Authorization' => 'Bearer ' . 'eJQ9935r68PRJzLEpF2xhkTCwykpgNhb4h799T4UjMc2KHx9oJw3pDKa4JAyO8MMtDAEMYlzRqR3WDYF',
        ];
        $data = [
            'mediaId' => "NLMmDVndR7",
            'auto' => $auto,
            'textFile' => UploadedFile::fake()->create('a1.txt'),
            'textBreakBy' => "sentence",
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
            'pmApprovalStatus' => 1,
        ];
        $response = $this->json('PUT', 'http://phoenix.localhost/api/v1/media/captions/original/update', $data, $auth);
        $response->assertStatus(200);
        $response->assertJson([
            "success" => true,
            "message" => config('constant.TRANSCRIPT_LIST_UPDATE_MESSAGE')
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

    // public function testGenearteManualTranslation()
    // {
    //     $this->genearteTranslation(false);
    // }

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
            'languageId' => 2,
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
}
