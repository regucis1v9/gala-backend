<?php
namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;
use Illuminate\Support\Facades\Storage;

class FolderControllerTest extends TestCase
{
    /** @test */
    public function it_can_create_a_folder()
    {
        $token = '1|9wayYpX8itpW7HBnsyMcC9pH4icP8REl8HXSzPml2b29141f';
        
        $credentials = [
            'folder_name' => 'mape',
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('api/createFolder', $credentials);

        $response->assertStatus(201);

    }

    /** @test */
    public function it_fails_to_create_a_folder_when_name_is_taken()
    {
        $token = '1|9wayYpX8itpW7HBnsyMcC9pH4icP8REl8HXSzPml2b29141f';

        $credentials = [
            'folder_name' => 'mape',
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('api/createFolder', $credentials);

        $response->assertStatus(400);
    }

    /** @test */
    public function it_fails_to_create_a_folder_when_validation_fails()
    {
        $token = '1|9wayYpX8itpW7HBnsyMcC9pH4icP8REl8HXSzPml2b29141f';

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('api/createFolder', []);

        $response->assertStatus(422);
        $response->assertJsonStructure(['errors']);
    }

    /** @test */
    public function it_can_list_folders()
    {
        $token = '1|9wayYpX8itpW7HBnsyMcC9pH4icP8REl8HXSzPml2b29141f';

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('api/listFolders');

        $response->assertStatus(200);
    }
    /** @test */
    public function it_fails_to_retrieve_files_when_folder_is_empty()
    {
        $token = '1|9wayYpX8itpW7HBnsyMcC9pH4icP8REl8HXSzPml2b29141f';

        $data = ['folder_name' => 'empty-folder'];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('api/retrieveFiles', $data);

        $response->assertStatus(404);
        $response->assertJson(['message' => 'No files found in this folder']);
    }

    /** @test */
    public function it_can_delete_a_folder()
    {
        $token = '1|9wayYpX8itpW7HBnsyMcC9pH4icP8REl8HXSzPml2b29141f';

        $data = ['folder_name' => 'mape'];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('api/deleteFolder', $data);

        $response->assertStatus(200);

    }

    /** @test */
    public function it_fails_to_delete_a_non_existent_folder()
    {
        $token = '1|9wayYpX8itpW7HBnsyMcC9pH4icP8REl8HXSzPml2b29141f';

        $data = ['folder_name' => 'non-existent-folder'];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('api/deleteFolder', $data);

        $response->assertStatus(404);
    }
}

