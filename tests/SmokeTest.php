<?php

namespace App\Tests;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;


class SmokeTest extends WebTestCase
{
    /**
     * Create a client with a default Authorization header.
     *
     * @param string $username
     * @param string $password
     *
     * @return \Symfony\Bundle\FrameworkBundle\Client
     */
    public function testCreateAuthenticatedClient($username = 'sebastien.cailleau.dev@gmail.com', $password = 'Salut123')
    {
        $client = static::createClient();

        $client->request(
            'POST',
            '/api/login_check',
            array(),
            array(),
            array('CONTENT_TYPE' => 'application/json'),
            json_encode(array(
                'username' => $username,
                'password' => $password,
            ))
        );

        $data = json_decode($client->getResponse()->getContent(), true);

        $this->assertResponseIsSuccessful();
        $this->assertArrayHasKey('token', $data);
        return $client;
    }

    public function testCheckActivation()
    {
        $client = static::createClient();
        $client->request(
            'POST',
            '/api/login/checkactivation',
            array(),
            array(),
            array('CONTENT_TYPE' => 'application/json'),
            json_encode(array(
                'username' => 'test@test.fr',
                'password' => 'Salut123',
            ))
        );

        $this->assertResponseIsSuccessful();
    }

    public function testUserProfil()
    {
        $client = $this->testCreateAuthenticatedClient();
        $data = json_decode($client->getResponse()->getContent(), true);

        $client->setServerParameter('HTTP_Authorization', sprintf('Bearer %s', $data['token']));
        $client->request(Request::METHOD_GET, '/api/user');
        $this->assertResponseIsSuccessful();
    }

    public function testGenerateUrl()
    {
        $client = $this->testCreateAuthenticatedClient();
        $data = json_decode($client->getResponse()->getContent(), true);

        $client->setServerParameter('HTTP_Authorization', sprintf('Bearer %s', $data['token']));
        $client->request('GET', '/api/generate_url/1');
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertResponseIsSuccessful();
        $this->assertArrayHasKey('url_token', $data);
        $this->assertArrayHasKey('id', $data);
        $this->assertJsonStringEqualsJsonString(
            json_encode(
                $data["id"]
            ),
            json_encode(
                1
            )
        );
    }

    public function testIndex()
    {
        $client = static::createClient();
        $client->request('GET', '/');

        $this->assertResponseIsSuccessful();
    }

    public function testUserNotConnect()
    {
        $client = static::createClient();
        $client->request('GET', '/api/user');

        $this->assertResponseStatusCodeSame('401');
    }

    public function testTravelWithToken()
    {
        $client = static::createClient();

        $client->request('GET', '/travels/1/03d5833a53bad0d9ae762424801d27f1', [
            'headers' => [
                'Accept' => 'application/json'
            ],
        ]);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertResponseStatusCodeSame('200');
        $this->assertJsonStringEqualsJsonString(
            json_encode(
                $data["id"]
            ),
            json_encode(
                1
            )
        );
        $this->assertJsonStringEqualsJsonString(
            json_encode(
                $data["title"]
            ),
            json_encode(
                'Mon voyage au Canada'
            )
        );
    }
}
