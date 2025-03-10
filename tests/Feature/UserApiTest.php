<?php
namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;
use Illuminate\Support\Facades\Storage;
use App\Models\User;

class UserControllerTest extends TestCase
{
    /** @test */
    public function it_can_create_a_user()
    {
        $token = '1|9wayYpX8itpW7HBnsyMcC9pH4icP8REl8HXSzPml2b29141f';

        $credentials = [
            'name' => 'test_user',
            'email' => 'testemail@test.com',
            'password' => 'test_password',
            'role' => 'Lietotājs',
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('api/createUser', $credentials);

        $response->assertStatus(201);
    }
    /** @test */
    public function it_can_get_all_users()
    {
        $token = '1|9wayYpX8itpW7HBnsyMcC9pH4icP8REl8HXSzPml2b29141f';
        

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('api/getAllUsers');

        $response->assertStatus(200);

    }
        /** @test */
    public function it_can_edit_a_user()
    {
        $token = '1|9wayYpX8itpW7HBnsyMcC9pH4icP8REl8HXSzPml2b29141f';
        $user = User::where('name', 'test_user')->first();

        $credentials = [
            'id' => $user->id,
            'name' => 'test_user1',
            'email' => 'testemail1@test.com',
            'password' => 'test1_password',
            'role' => 'Administrators',
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('api/editUser', $credentials);

        $response->assertStatus(200);
    }
            /** @test */
            public function it_can_delete_a_user()
            {
                $token = '1|9wayYpX8itpW7HBnsyMcC9pH4icP8REl8HXSzPml2b29141f';
        
                $user = User::where('name', 'test_user1')->first();
    
                $credentials = [
                    'id' => $user->id,
                ];
        
                $response = $this->withHeaders([
                    'Authorization' => 'Bearer ' . $token,
                ])->postJson('api/deleteUser', $credentials);
        
                $response->assertStatus(200);
            }
}

