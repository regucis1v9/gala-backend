<?php
namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;
use Illuminate\Support\Facades\Storage;

class ScreenControllerTest extends TestCase
{
    /** @test */
    public function it_can_create_a_screen()
    {
        $token = '1|9wayYpX8itpW7HBnsyMcC9pH4icP8REl8HXSzPml2b29141f';
        

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('api/addScreen');

        $response->assertStatus(201);

    }

    /** @test */
    public function it_can_get_all_screens()
    {
        $token = '1|9wayYpX8itpW7HBnsyMcC9pH4icP8REl8HXSzPml2b29141f';
        

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('api/addScreen');

        $response->assertStatus(201);

    }

    /** @test */
    public function it_can_delete_a_screen()
    {
        $token = '1|9wayYpX8itpW7HBnsyMcC9pH4icP8REl8HXSzPml2b29141f';

        $credentials = [
            'id' => '1',
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('api/deleteScreen', $credentials);

        $response->assertStatus(200);
    }
}

