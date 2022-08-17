<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserTest extends TestCase
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
}
