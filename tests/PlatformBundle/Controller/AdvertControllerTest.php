<?php

namespace Tests\PlatformBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AdvertControllerTest extends WebTestCase
{
    public function testHello(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/fr/platform');

        $this->assertContains('Liste des annonces', $client->getResponse()->getContent());
    }
}
