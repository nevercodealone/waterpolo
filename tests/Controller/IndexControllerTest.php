<?php


namespace App\Tests\Controller;


use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class IndexControllerTest extends WebTestCase
{



    public function testStatusForRoutes()
    {
        $client = static::createClient([], [
            'HTTP_HOST'       => '127.0.0.1:8000'
        ]);

        $client->request('GET', '/videos/');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }

    public function testNewsHeadline()
    {
        $client = static::createClient([], [
            'HTTP_HOST'       => '127.0.0.1:8000'
        ]);

        $client->request('GET', '/');

        $this->assertSelectorTextContains('h1', 'WP News');
    }

    public function testNewsGreaterTwo()
    {
        $client = static::createClient([], [
            'HTTP_HOST'       => '127.0.0.1:8000'
        ]);

        $crawler = $client->request('GET', '/');


        $this->assertGreaterThan(
            2,
            $crawler->filter('h2')->count()
        );
    }

}
