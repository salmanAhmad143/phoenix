<?php

namespace Tests\Unit;

use Tests\TestCase;

class ProjectTest extends TestCase
{
    public function testStore()
    {
        $this->withoutExceptionHandling();
        $auth = [
            'HTTP_Authorization' => 'Bearer ' . 'eJQ9935r68PRJzLEpF2xhkTCwykpgNhb4h799T4UjMc2KHx9oJw3pDKa4JAyO8MMtDAEMYlzRqR3WDYF',
        ];
        $data = [
            'name' => "Project4",
            'workflowId' => '',
        ];
        $response = $this->json('POST', 'http://phoenix.localhost/api/v1/projects/create', $data, $auth);
        $response->assertStatus(200);
        $response->assertJson([
            "success" => true,
            "message" => config('constant.PROJECT_CREATE_MESSAGE')
        ]);
    }

    public function testIndex()
    {
        $this->withoutExceptionHandling();
        $auth = [
            'HTTP_Authorization' => 'Bearer ' . 'eJQ9935r68PRJzLEpF2xhkTCwykpgNhb4h799T4UjMc2KHx9oJw3pDKa4JAyO8MMtDAEMYlzRqR3WDYF',
        ];
        $data = [
            'page' => 1,
            'size' => 4,
        ];
        $response = $this->json('GET', 'http://phoenix.localhost/api/v1/projects', $data, $auth);
        $response->assertStatus(200);
        $response->assertJsonStructure([
            "success",
            "data" => ["projects"]
        ]);
    }

    public function testShow()
    {
        $this->withoutExceptionHandling();
        $auth = [
            'HTTP_Authorization' => 'Bearer ' . 'eJQ9935r68PRJzLEpF2xhkTCwykpgNhb4h799T4UjMc2KHx9oJw3pDKa4JAyO8MMtDAEMYlzRqR3WDYF',
        ];
        $data = [
            'projectId' => 'NLMmDVndR7',
        ];
        $response = $this->json('GET', 'http://phoenix.localhost/api/v1/projects/details', $data, $auth);
        $response->assertStatus(200);
        $response->assertJsonStructure([
            "success",
            "data" => ["projects"]
        ]);
    }

    public function testUpdate()
    {
        $this->withoutExceptionHandling();
        $auth = [
            'HTTP_Authorization' => 'Bearer ' . 'eJQ9935r68PRJzLEpF2xhkTCwykpgNhb4h799T4UjMc2KHx9oJw3pDKa4JAyO8MMtDAEMYlzRqR3WDYF',
        ];
        $data = [
            'projectId' => "NLMmDVndR7",
            'name' => "Project1",
            'workflowId' => '',
        ];
        $response = $this->json('PUT', 'http://phoenix.localhost/api/v1/projects/update', $data, $auth);
        $response->assertStatus(200);
        $response->assertJson([
            "success" => true,
            "message" => config('constant.PROJECT_UPDATE_MESSAGE')
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
        ];
        $response = $this->json('DELETE', 'http://phoenix.localhost/api/v1/projects/delete', $data, $auth);
        $response->assertStatus(200);
        $response->assertJson([
            "success" => true,
            "message" => config('constant.PROJECT_DELETE_MESSAGE')
        ]);
    }
}
