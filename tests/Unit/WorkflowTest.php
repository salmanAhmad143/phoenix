<?php

namespace Tests\Feature;

use Tests\TestCase;

class WorkflowTest extends TestCase
{
    public function testIndex()
    {
        $this->withoutExceptionHandling();
        $auth = [
            'HTTP_Authorization' => 'Bearer ' . 'eJQ9935r68PRJzLEpF2xhkTCwykpgNhb4h799T4UjMc2KHx9oJw3pDKa4JAyO8MMtDAEMYlzRqR3WDYF',
        ];
        $data = [];
        $response = $this->json('GET', 'http://phoenix.localhost/api/v1/workflow', $data, $auth);
        $response->assertStatus(200);
        $response->assertJson([
            "success" => true,
        ]);
        $response->assertJsonStructure([
            "success",
            "data" => ["workflow"]
        ]);
    }
}
