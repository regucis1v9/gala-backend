<?php
namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;
use Illuminate\Support\Facades\Storage;
use App\Models\User;

class SlideControllerTest extends TestCase
{
    /** @test */
    public function it_can_create_a_slide()
    {
        $token = '1|9wayYpX8itpW7HBnsyMcC9pH4icP8REl8HXSzPml2b29141f';

        $credentials = [
            [
            'imageLink' => 'http://localhost/storage/IPa21/110316_logo.png',
            'description' => 'test',
            'textColor' => 'rgba(255, 255, 255, 1)',
            'bgColor' => 'rgba(0, 0, 0, 1)',
            'textPosition' => 'middle-center',
            'startDate' => "2024-10-22T00:00:00.000Z",
            'endDate' => "2024-10-23T00:00:00.000Z",
            'selectedScreens' => ['1'],
            ]
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('api/saveSlides', $credentials);

        $response->assertStatus(201);
    }
        /** @test */
        public function it_can_get_all_slides()
        {
            $token = '1|9wayYpX8itpW7HBnsyMcC9pH4icP8REl8HXSzPml2b29141f';
            
    
            $response = $this->withHeaders([
                'Authorization' => 'Bearer ' . $token,
            ])->getJson('api/getAllSlides');
    
            $response->assertStatus(200);
    
        }
                /** @test */
                public function it_can_get_todays_slides()
                {
                    $token = '1|9wayYpX8itpW7HBnsyMcC9pH4icP8REl8HXSzPml2b29141f';
                    
            
                    $response = $this->withHeaders([
                        'Authorization' => 'Bearer ' . $token,
                    ])->getJson('api/getTodaysSlides');
            
                    $response->assertStatus(200);
            
                }
}

