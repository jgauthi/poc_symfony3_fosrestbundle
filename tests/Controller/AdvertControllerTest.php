<?php

namespace App\Tests\Controller;

use App\DataFixtures\UserFixtures;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;

class AdvertControllerTest extends WebTestCase
{
    private const ROUTE = [
        'index' =>  '/fr/platform',
        'list'  =>  '/fr/platform/list',
        'add'   =>  '/fr/platform/add',
        'login' =>  '/login',
        'login_check' =>  '/login_check',
    ];
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;
    /**
     * @var ContainerInterface
     */
    private $container;

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
        $this->assertGreaterThanOrEqual(
            3,
            count($json['listsAdvert']),
            'The json file displays the right number of adverts.'
        );
    }
}
