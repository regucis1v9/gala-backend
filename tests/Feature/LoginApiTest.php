<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class LoginTest extends TestCase
{
    /** @test */
    public function it_can_login()
    {
        $credentials = [
            'name' => 'admin',
            'password' => 'undemarsucks',
        ];

        $response = $this->postJson('api/login', $credentials);


        $response->assertStatus(200);
    }

    /** @test */
    public function it_fails_when_user_is_invalid()
    {
        $credentials = [
            'name' => 'wronguser', 
            'password' => 'undemarsucks', 
        ];

        $response = $this->postJson('api/login', $credentials);

        // Assert that the response status is 422
        $response->assertStatus(422);
    }
        /** @test */
        public function it_fails_when_password_is_invalid()
        {
            $credentials = [
                'name' => 'admin', 
                'password' => '123123123', 
            ];
    
            $response = $this->postJson('api/login', $credentials);
    
            // Assert that the response status is 422
            $response->assertStatus(401);
        }
}
