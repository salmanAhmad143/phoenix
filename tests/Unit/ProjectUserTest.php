<?php

namespace Tests\Unit;

use Tests\TestCase;

class ProjectUserTest extends TestCase
{
    public function testIndex()
    {
        $this->withoutExceptionHandling();
        $auth = [
            'HTTP_Authorization' => 'Bearer ' . 'eJQ9935r68PRJzLEpF2xhkTCwykpgNhb4h799T4UjMc2KHx9oJw3pDKa4JAyO8MMtDAEMYlzRqR3WDYF',
        ];
        $data = [
            'projectId' => "NLMmDVndR7"
        ];
        $response = $this->json('GET', 'http://phoenix.localhost/api/v1/project/user', $data, $auth);
        $response->assertStatus(200);
        $response->assertJsonStructure([
            "success",
            "data" => ["users"]
        ]);
    }

    public function testStore()
    {
        $this->withoutExceptionHandling();
        $auth = [
            'HTTP_Authorization' => 'Bearer ' . 'eJQ9935r68PRJzLEpF2xhkTCwykpgNhb4h799T4UjMc2KHx9oJw3pDKa4JAyO8MMtDAEMYlzRqR3WDYF',
        ];
        $data = [
            'projectId' => "NLMmDVndR7",
            'userId' => ['NLMmDVndR7', 'Jpyn5kmvYX'],
        ];
        $response = $this->json('POST', 'http://phoenix.localhost/api/v1/project/user/add', $data, $auth);
        $response->assertStatus(200);
        $response->assertJson([
            "success" => true,
            "message" => config('constant.PROJECT_USER_ADD_MESSAGE')
        ]);
    }

    public function testDestroy()
    {
        $this->withoutExceptionHandling();
        $auth = [
            'HTTP_Authorization' => 'Bearer ' . 'eJQ9935r68PRJzLEpF2xhkTCwykpgNhb4h799T4UjMc2KHx9oJw3pDKa4JAyO8MMtDAEMYlzRqR3WDYF',
        ];
        $data = [
            'projectId' => "NLMmDVndR7",
            'userId' => "Jpyn5kmvYX",
        ];
        $response = $this->json('DELETE', 'http://phoenix.localhost/api/v1/project/user/remove', $data, $auth);
        $response->assertStatus(200);
        $response->assertJson([
            "success" => true,
            "message" => config('constant.PROJECT_USER_REMOVE_MESSAGE')
        ]);
    }
}
