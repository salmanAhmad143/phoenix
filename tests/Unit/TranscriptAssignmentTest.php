<?php

namespace Tests\Unit;

use Tests\TestCase;

class TranscriptAssignmentTest extends TestCase
{
    public function testGetTranscriptTransition()
    {
        $this->withoutExceptionHandling();
        $auth = [
            'HTTP_Authorization' => 'Bearer ' . 'eJQ9935r68PRJzLEpF2xhkTCwykpgNhb4h799T4UjMc2KHx9oJw3pDKa4JAyO8MMtDAEMYlzRqR3WDYF',
        ];
        $data = [];
        $response = $this->json('GET', 'http://phoenix.localhost/api/v1/media/transcript/assignment/workflow-transition?workflowId=1&workflowStateId=1&currentStateStatus=unassigned', $data, $auth);
        $response->assertStatus(200);
        // $response->assertJson([
        //     "success" => true,
        //     "message" => config('constant.ASSIGNMENT_USER_ASSIGNED_MESSAGE')
        // ]);
        $response->assertJsonStructure([
            "success",
            "data" => ["workflowTransition"]
        ]);
    }

    public function testTranscriptAssignment()
    {
        $this->withoutExceptionHandling();
        $auth = [
            'HTTP_Authorization' => 'Bearer ' . 'eJQ9935r68PRJzLEpF2xhkTCwykpgNhb4h799T4UjMc2KHx9oJw3pDKa4JAyO8MMtDAEMYlzRqR3WDYF',
        ];
        $data = [
            'mediaId' => "NLMmDVndR7",
            'languageId' => "29",
            'workflowTransitionId' => "1",
            'workflowStateId' => "2",
            'linguistId' => "NLMmDVndR7",
            'cost' => "20000",
            'unit' => "Per Char",
            'currency' => "INR",
        ];
        $response = $this->json('POST', 'http://phoenix.localhost/api/v1/media/transcript/assignment', $data, $auth);
        $response->assertStatus(200);
        $response->assertJson([
            "success" => true,
            "message" => config('constant.ASSIGNMENT_USER_ASSIGNED_MESSAGE')
        ]);
    }
}
