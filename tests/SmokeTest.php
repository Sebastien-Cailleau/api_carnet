<?php

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class SmokeTest extends WebTestCase
{
    public function testUser()
    {
        $client = static::createClient();
        $client->request(
            'GET',
            '/api/login_check',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
            ]
        );
        $this->assertResponseIsSuccessful();
    }
}
