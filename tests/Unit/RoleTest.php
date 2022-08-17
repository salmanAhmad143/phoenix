<?php

namespace Tests\Unit;

use Tests\TestCase;

class RoleTest extends TestCase
{
    public function testIndex()
    {
        $this->withoutExceptionHandling();
        $auth = [
            'HTTP_Authorization' => 'Bearer ' . 'eJQ9935r68PRJzLEpF2xhkTCwykpgNhb4h799T4UjMc2KHx9oJw3pDKa4JAyO8MMtDAEMYlzRqR3WDYF',
        ];
        $data = [];
        $response = $this->json('GET', 'http://phoenix.localhost/api/v1/roles', $data, $auth);
        $response->assertStatus(200);
        $response->assertJsonStructure([
            "success",
            "data" => ["roles"]
        ]);
    }

    public function testStore()
    {
        $this->withoutExceptionHandling();
        $auth = [
            'HTTP_Authorization' => 'Bearer ' . 'eJQ9935r68PRJzLEpF2xhkTCwykpgNhb4h799T4UjMc2KHx9oJw3pDKa4JAyO8MMtDAEMYlzRqR3WDYF',
        ];
        $data = [
            'name' => "Admin",
            'description' => "Admin has all administrative permission",
            'permissions' => [
                [
                    'contentId' => 1,
                    'actions' => ['canView' => 1, 'canAdd' => 1, 'canEdit' => 1, 'canDelete' => 1, 'canDownload' => 1,]
                ],
                [
                    'contentId' => 2,
                    'actions' => ['canView' => 1, 'canAdd' => 1, 'canEdit' => 1, 'canDelete' => 1, 'canDownload' => 1,]
                ],
                [
                    'contentId' => 3,
                    'actions' => ['canView' => 1, 'canAdd' => 1, 'canEdit' => 1, 'canDelete' => 1, 'canDownload' => 1,]
                ],
                [
                    'contentId' => 4,
                    'actions' => ['canView' => 1, 'canAdd' => 1, 'canEdit' => 1, 'canDelete' => 1, 'canDownload' => 1,]
                ],
                [
                    'contentId' => 5,
                    'actions' => ['canView' => 1, 'canAdd' => 1, 'canEdit' => 1, 'canDelete' => 1, 'canDownload' => 1,]
                ],
                [
                    'contentId' => 6,
                    'actions' => ['canView' => 1, 'canAdd' => 1, 'canEdit' => 1, 'canDelete' => 1, 'canDownload' => 1,]
                ],
                [
                    'contentId' => 7,
                    'actions' => ['canView' => 1, 'canAdd' => 1, 'canEdit' => 1, 'canDelete' => 1, 'canDownload' => 1,]
                ],
                [
                    'contentId' => 8,
                    'actions' => ['canView' => 1, 'canAdd' => 1, 'canEdit' => 1, 'canDelete' => 1, 'canDownload' => 1,]
                ],
                [
                    'contentId' => 9,
                    'actions' => ['canView' => 1, 'canAdd' => 1, 'canEdit' => 1, 'canDelete' => 1, 'canDownload' => 1,]
                ],
                [
                    'contentId' => 10,
                    'actions' => ['canView' => 1, 'canAdd' => 1, 'canEdit' => 1, 'canDelete' => 1, 'canDownload' => 1,]
                ],
                [
                    'contentId' => 11,
                    'actions' => ['canView' => 1, 'canAdd' => 1, 'canEdit' => 1, 'canDelete' => 1, 'canDownload' => 1,]
                ],
                [
                    'contentId' => 12,
                    'actions' => ['canView' => 1, 'canAdd' => 1, 'canEdit' => 1, 'canDelete' => 1, 'canDownload' => 1,]
                ],
                [
                    'contentId' => 13,
                    'actions' => ['canView' => 1, 'canAdd' => 1, 'canEdit' => 1, 'canDelete' => 1, 'canDownload' => 1,]
                ],
            ]
        ];
        $response = $this->json('POST', 'http://phoenix.localhost/api/v1/roles/create', $data, $auth);
        $response->assertStatus(200);
        $response->assertJson([
            "success" => true,
            "message" => config('constant.ROLE_CREATE_MESSAGE')
        ]);
    }

    public function testShow()
    {
        $this->withoutExceptionHandling();
        $auth = [
            'HTTP_Authorization' => 'Bearer ' . 'eJQ9935r68PRJzLEpF2xhkTCwykpgNhb4h799T4UjMc2KHx9oJw3pDKa4JAyO8MMtDAEMYlzRqR3WDYF',
        ];
        $data = [
            'roleId' => "NLMmDVndR7",
        ];
        $response = $this->json('GET', 'http://phoenix.localhost/api/v1/roles/details', $data, $auth);
        $response->assertStatus(200);
        $response->assertJsonStructure([
            "success",
            "data"
        ]);
    }

    public function testUserRolesDetails()
    {
        $this->withoutExceptionHandling();
        $auth = [
            'HTTP_Authorization' => 'Bearer ' . 'eJQ9935r68PRJzLEpF2xhkTCwykpgNhb4h799T4UjMc2KHx9oJw3pDKa4JAyO8MMtDAEMYlzRqR3WDYF',
        ];
        $data = [];
        $response = $this->json('GET', 'http://phoenix.localhost/api/v1/user/roles/details', $data, $auth);
        $response->assertStatus(200);
        $response->assertJsonStructure([
            "success",
            "data" => [
                "name",
                "email",
                "permission"
            ]
        ]);
    }

    public function testUpdate()
    {
        $this->withoutExceptionHandling();
        $auth = [
            'HTTP_Authorization' => 'Bearer ' . 'eJQ9935r68PRJzLEpF2xhkTCwykpgNhb4h799T4UjMc2KHx9oJw3pDKa4JAyO8MMtDAEMYlzRqR3WDYF',
        ];
        $data = [
            'roleId' => "NLMmDVndR7",
            'name' => "Admin",
            'description' => "Admin has all administrative permissions",
            'permissions' => [
                [
                    'contentId' => 1,
                    'actions' => ['canView' => 1, 'canAdd' => 1, 'canEdit' => 1, 'canDelete' => 1, 'canDownload' => 1,]
                ],
                [
                    'contentId' => 2,
                    'actions' => ['canView' => 1, 'canAdd' => 1, 'canEdit' => 1, 'canDelete' => 1, 'canDownload' => 1,]
                ],
                [
                    'contentId' => 3,
                    'actions' => ['canView' => 1, 'canAdd' => 1, 'canEdit' => 1, 'canDelete' => 1, 'canDownload' => 1,]
                ],
                [
                    'contentId' => 4,
                    'actions' => ['canView' => 1, 'canAdd' => 1, 'canEdit' => 1, 'canDelete' => 1, 'canDownload' => 1,]
                ],
                [
                    'contentId' => 5,
                    'actions' => ['canView' => 1, 'canAdd' => 1, 'canEdit' => 1, 'canDelete' => 1, 'canDownload' => 1,]
                ],
                [
                    'contentId' => 6,
                    'actions' => ['canView' => 1, 'canAdd' => 1, 'canEdit' => 1, 'canDelete' => 1, 'canDownload' => 1,]
                ],
                [
                    'contentId' => 7,
                    'actions' => ['canView' => 1, 'canAdd' => 1, 'canEdit' => 1, 'canDelete' => 1, 'canDownload' => 1,]
                ],
                [
                    'contentId' => 8,
                    'actions' => ['canView' => 1, 'canAdd' => 1, 'canEdit' => 1, 'canDelete' => 1, 'canDownload' => 1,]
                ],
                [
                    'contentId' => 9,
                    'actions' => ['canView' => 1, 'canAdd' => 1, 'canEdit' => 1, 'canDelete' => 1, 'canDownload' => 1,]
                ],
                [
                    'contentId' => 10,
                    'actions' => ['canView' => 1, 'canAdd' => 1, 'canEdit' => 1, 'canDelete' => 1, 'canDownload' => 1,]
                ],
                [
                    'contentId' => 11,
                    'actions' => ['canView' => 1, 'canAdd' => 1, 'canEdit' => 1, 'canDelete' => 1, 'canDownload' => 1,]
                ],
                [
                    'contentId' => 12,
                    'actions' => ['canView' => 1, 'canAdd' => 1, 'canEdit' => 1, 'canDelete' => 1, 'canDownload' => 1,]
                ],
                [
                    'contentId' => 13,
                    'actions' => ['canView' => 1, 'canAdd' => 1, 'canEdit' => 1, 'canDelete' => 1, 'canDownload' => 1,]
                ],
            ]
        ];
        $response = $this->json('PUT', 'http://phoenix.localhost/api/v1/roles/update', $data, $auth);
        $response->assertStatus(200);
        $response->assertJson([
            "success" => true,
            "message" => config('constant.ROLE_UPDATE_MESSAGE')
        ]);
    }

    public function testDestroy()
    {
        $this->withoutExceptionHandling();
        $auth = [
            'HTTP_Authorization' => 'Bearer ' . 'eJQ9935r68PRJzLEpF2xhkTCwykpgNhb4h799T4UjMc2KHx9oJw3pDKa4JAyO8MMtDAEMYlzRqR3WDYF',
        ];
        $data = [
            'roleId' => "NLMmDVndR7",
        ];
        $response = $this->json('DELETE', 'http://phoenix.localhost/api/v1/roles/delete', $data, $auth);
        $response->assertStatus(200);
        $response->assertJson([
            "success" => true,
            "message" => config('constant.ROLE_DELETE_MESSAGE')
        ]);
    }
}
