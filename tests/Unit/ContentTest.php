<?php

namespace Tests\Unit;

use Tests\TestCase;

class ContentTest extends TestCase
{
    public function testIndex()
    {
        $this->withoutExceptionHandling();
        $auth = [
            'HTTP_Authorization' => 'Bearer ' . 'eJQ9935r68PRJzLEpF2xhkTCwykpgNhb4h799T4UjMc2KHx9oJw3pDKa4JAyO8MMtDAEMYlzRqR3WDYF',
        ];
        $data = [
            'name' => "Rol",
        ];
        $response = $this->json('GET', 'http://phoenix.localhost/api/v1/content', $data, $auth);
        $response->assertStatus(200);
        $response->assertJsonStructure([
            "success",
            "data" => ["contents"]
        ]);
    }
}
