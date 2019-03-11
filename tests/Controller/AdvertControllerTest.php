<?php
namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class AdvertControllerTest extends WebTestCase
{
    private const ROUTE = [
        'index' =>  '/fr/platform',
        'list'  =>  '/fr/platform/list',
        'add'   =>  '/fr/platform/add',
        'login' =>  '/login',
        'login_check' =>  '/login_check',
    ];

    public function testIndex(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', self::ROUTE['index']);

        $this->assertSame(
            Response::HTTP_OK,
            $client->getResponse()->getStatusCode(),
            sprintf('The %s public URL loads correctly.', self::ROUTE['index'])
        );
        $this->assertContains('Liste des annonces', $client->getResponse()->getContent());

        $this->assertLessThanOrEqual(
            3,
            count($crawler->filter('#content ul')->first()->filter('li')),
            'The homepage displays the right number of adverts.'
        );
    }

    // Test json advert List
    public function testList(): void
    {
        $client = static::createClient();
        $client->request('GET', self::ROUTE['list']);
        $json = json_decode($client->getResponse()->getContent(), true);

        $this->assertSame(
            Response::HTTP_OK,
            $client->getResponse()->getStatusCode(),
            sprintf('The %s public URL loads correctly.', self::ROUTE['list'])
        );
        $this->assertSame(
            'application/json',
            $client->getResponse()->headers->get('Content-Type')
        );

        $this->assertArrayHasKey('listsAdvert', $json);
        $this->assertGreaterThan(
            1,
            count($json['listsAdvert']),
            'The json file displays the right number of adverts.'
        );

        $this->assertLessThanOrEqual(
            3,
            count($json['listsAdvert']),
            'The json file displays the right number of adverts.'
        );
    }
}
