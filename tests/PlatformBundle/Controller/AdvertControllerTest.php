<?php
namespace Tests\PlatformBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AdvertController extends WebTestCase
{
    public function testHello()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/fr/platform');

        $this->assertContains('Liste des annonces', $client->getResponse()->getContent());
    }
}
