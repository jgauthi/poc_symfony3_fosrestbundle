<?php

namespace Tests\PlatformBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AdvertController extends WebTestCase
{
    public function testHello()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/fr/platform/hello');

        $this->assertContains('Hello World', $client->getResponse()->getContent());
    }
}
