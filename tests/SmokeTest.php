<?php

namespace App\Tests;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class SmokeTest extends WebTestCase
{
    /**
     * Create a client with a default Authorization header.
     * Test apiLoginCheck method
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

    /**
     * Test CheckActivation method
     *
     * @return void
     */
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

    /**
     * Test access to user profil page
     *
     * @return void
     */
    public function testUserProfil()
    {
        $client = $this->testCreateAuthenticatedClient();
        $data = json_decode($client->getResponse()->getContent(), true);

        $client->setServerParameter('HTTP_Authorization', sprintf('Bearer %s', $data['token']));
        $client->request(Request::METHOD_GET, '/api/user');
        $this->assertResponseIsSuccessful();
    }

    /**
     * Test GeneratorContorller
     *
     * @return void
     */
    public function testGenerateUrl()
    {
        $client = $this->testCreateAuthenticatedClient();
        $dataClient = json_decode($client->getResponse()->getContent(), true);
        // set request parameter
        $client->setServerParameter('HTTP_Authorization', sprintf('Bearer %s', $dataClient['token']));
        // send request
        $client->request('GET', '/api/generate_url/1');
        // decode JSON response
        $dataResponse = json_decode($client->getResponse()->getContent(), true);
        // test response
        $this->assertResponseIsSuccessful();
        // test presence of key in array $data
        $this->assertArrayHasKey('url_token', $dataResponse);
        $this->assertArrayHasKey('id', $dataResponse);
        //  test the equality of the answer with that expected
        $this->assertJsonStringEqualsJsonString(
            json_encode(
                $dataResponse["id"]
            ),
            json_encode(
                1
            )
        );
    }

    /**
     * Test access to one travel
     *
     * @return void
     */
    public function testGetTravel()
    {
        $client = $this->testCreateAuthenticatedClient();
        $dataClient = json_decode($client->getResponse()->getContent(), true);

        $client->setServerParameter('HTTP_Authorization', sprintf('Bearer %s', $dataClient['token']));
        $client->request('GET', '/api/travels/4');

        $dataResponse = json_decode($client->getResponse()->getContent(), true);

        $this->assertResponseStatusCodeSame('200');
        $this->assertJsonStringEqualsJsonString(
            json_encode(
                $dataResponse["id"]
            ),
            json_encode(
                4
            )
        );
        $this->assertJsonStringEqualsJsonString(
            json_encode(
                $dataResponse["title"]
            ),
            json_encode(
                'La Corse à moto'
            )
        );
    }

    /**
     * Test API home page
     *
     * @return void
     */
    public function testIndex()
    {
        $client = static::createClient();
        $client->request('GET', '/');

        $this->assertSelectorTextContains('html h1', 'API Carnet');
        $this->assertResponseIsSuccessful();
    }

    /**
     * Test access refused for no authenticated user
     *
     * @return void
     */
    public function testUserNotConnect()
    {
        $client = static::createClient();
        $client->request('GET', '/api/user');

        $this->assertResponseStatusCodeSame('401');
        $this->assertResponseHeaderSame('Content-Type', 'application/json');
    }

    /**
     * Test access to travel for visitor (no authentication)
     *
     * @return void
     */
    public function testTravelWithToken()
    {
        $client = static::createClient();
        $client->request('GET', '/travels/1/03d5833a53bad0d9ae762424801d27f1');

        $dataResponse = json_decode($client->getResponse()->getContent(), true);

        $this->assertResponseStatusCodeSame('200');
        $this->assertJsonStringEqualsJsonString(
            json_encode(
                $dataResponse["id"]
            ),
            json_encode(
                1
            )
        );
        $this->assertJsonStringEqualsJsonString(
            json_encode(
                $dataResponse["title"]
            ),
            json_encode(
                'Mon voyage au Canada'
            )
        );
    }
}
