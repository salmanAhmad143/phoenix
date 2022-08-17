<?php

namespace Tests\Unit;

use Tests\TestCase;

class TeamTest extends TestCase
{
    public function testIndex()
    {
        $this->withoutExceptionHandling();
        $auth = [
            'HTTP_Authorization' => 'Bearer ' . 'eJQ9935r68PRJzLEpF2xhkTCwykpgNhb4h799T4UjMc2KHx9oJw3pDKa4JAyO8MMtDAEMYlzRqR3WDYF',
        ];
        $data = [];
        $response = $this->json('GET', 'http://phoenix.localhost/api/v1/team', $data, $auth);
        $response->assertStatus(200);
        $response->assertJsonStructure([
            "success",
            "data" => ["team"]
        ]);
    }

    public function testStore()
    {
        $this->withoutExceptionHandling();
        $auth = [
            'HTTP_Authorization' => 'Bearer ' . 'eJQ9935r68PRJzLEpF2xhkTCwykpgNhb4h799T4UjMc2KHx9oJw3pDKa4JAyO8MMtDAEMYlzRqR3WDYF',
        ];
        $data = [
            'name' => "Project Manager",
        ];
        $response = $this->json('POST', 'http://phoenix.localhost/api/v1/team/create', $data, $auth);
        $response->assertStatus(200);
        $response->assertJson([
            "success" => true,
            "message" => config('constant.TEAM_CREATE_MESSAGE')
        ]);
    }

    public function testUpdate()
    {
        $this->withoutExceptionHandling();
        $auth = [
            'HTTP_Authorization' => 'Bearer ' . 'eJQ9935r68PRJzLEpF2xhkTCwykpgNhb4h799T4UjMc2KHx9oJw3pDKa4JAyO8MMtDAEMYlzRqR3WDYF',
        ];
        $data = [
            'teamId' => "NLMmDVndR7",
            'name' => "Linguist",
        ];
        $response = $this->json('PUT', 'http://phoenix.localhost/api/v1/team/update', $data, $auth);
        $response->assertStatus(200);
        $response->assertJson([
            "success" => true,
            "message" => config('constant.TEAM_UPDATE_MESSAGE')
        ]);
    }

    public function testDestroy()
    {
        $this->withoutExceptionHandling();
        $auth = [
            'HTTP_Authorization' => 'Bearer ' . 'eJQ9935r68PRJzLEpF2xhkTCwykpgNhb4h799T4UjMc2KHx9oJw3pDKa4JAyO8MMtDAEMYlzRqR3WDYF',
        ];
        $data = [
            'teamId' => "NLMmDVndR7",
        ];
        $response = $this->json('DELETE', 'http://phoenix.localhost/api/v1/team/delete', $data, $auth);
        $response->assertStatus(200);
        $response->assertJson([
            "success" => true,
            "message" => config('constant.TEAM_DELETE_MESSAGE')
        ]);
    }
}
