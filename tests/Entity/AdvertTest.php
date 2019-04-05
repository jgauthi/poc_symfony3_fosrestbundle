<?php

namespace App\Tests\Entity;

use App\Entity\Advert;
use App\Entity\Application;
use App\Entity\Image;
use App\Entity\User;
use PHPUnit\Framework\TestCase;

class AdvertTest extends TestCase
{
    private function initAdvert(): Advert
    {
        $user = new User();
        $user->setUsername('john-snow');

        $advert = new Advert();
        $advert->setAuthor($user)
            ->setTitle('MyTestTitle')
            ->setContent('MyTestContent')
            ->setPublished(false)
            ->setDate(\DateTime::createFromFormat('Y-m-d', '2018-07-01'));

        for ($i = 0; $i < 3; ++$i) {
            $application = new Application();
            $application->setAuthor($user);

            $advert->addApplication($application);
            $advert->increaseApplication();
        }

        return $advert;
    }

    public function testSameTitle(): void
    {
        $advert = $this->initAdvert();

        $this->assertSame('MyTestTitle', $advert->getTitle());
    }

    public function testContainContent(): void
    {
        $advert = $this->initAdvert();

        $this->assertContains('Content', $advert->getContent());
    }

    public function testNotPublished(): void
    {
        $advert = $this->initAdvert();

        $this->assertFalse($advert->getPublished());
    }

    public function testIncorrectCreationDate(): void
    {
        $advert = new Advert();

        $this->expectException('LogicException');
        $advert->setDate(new \DateTime('2015-01-01'));
    }

    public function testUpdateDateIsNull(): void
    {
        $advert = $this->initAdvert();

        $this->assertNull($advert->getUpdatedAt());
    }

    public function testNbApplication(): void
    {
        $advert = $this->initAdvert();

        $this->assertCount(3, $advert->getApplications());
    }

    public function testNbApplicationGreaterThan2(): void
    {
        $advert = $this->initAdvert();

        $this->assertGreaterThan(2, $advert->getNbApplications());
    }

    public function testNoCategory(): void
    {
        $advert = $this->initAdvert();

        $this->assertEmpty($advert->getCategories());
    }

    public function testReturnImageClass(): void
    {
        $advert = new Advert();
        $advert->setImage(new Image());

        $this->assertInstanceOf(Image::class, $advert->getImage());
    }
}
