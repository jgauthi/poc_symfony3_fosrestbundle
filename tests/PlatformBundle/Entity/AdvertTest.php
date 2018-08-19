<?php
namespace Tests\PlatformBundle\Entity;

use PlatformBundle\Entity\Advert;
use PlatformBundle\Entity\Application;
use PHPUnit\Framework\TestCase;

class AdvertTest extends TestCase
{
    private function initAdvert()
    {
        $advert = new Advert;

        $advert->setAuthor('John Snow')
            ->setTitle('MyTestTitle')
            ->setContent('MyTestContent')
            ->setPublished(false)
            ->setDate( \DateTime::createFromFormat('Y-m-d', '2018-07-01') );

        for($i = 0; $i < 3; $i++)
        {
            $application = new Application;
            $application->setAuthor("Someone #{$i}");

            $advert->addApplication($application);
            $advert->increaseApplication();
        }

        return $advert;
    }

    public function testSameTitle()
    {
        $advert = $this->initAdvert();

        $this->assertSame('MyTestTitle', $advert->getTitle());
    }

    public function testContainContent()
    {
        $advert = $this->initAdvert();

        $this->assertContains('Content', $advert->getContent());
    }

    public function testNotPublished()
    {
        $advert = $this->initAdvert();

        $this->assertFalse($advert->getPublished());
    }

    public function testIncorrectCreationDate()
    {
        $advert = new Advert;

        $this->expectException('LogicException');
        $advert->setDate( new \DateTime('2015-01-01') );
    }

    public function testUpdateDateIsNull()
    {
        $advert = $this->initAdvert();

        $this->assertNull($advert->getUpdatedAt());
    }

    public function testNbApplication()
    {
        $advert = $this->initAdvert();

        $this->assertCount(3, $advert->getApplications());
    }

    public function testNbApplicationGreaterThan2()
    {
        $advert = $this->initAdvert();

        $this->assertGreaterThan(2, $advert->getNbApplications());
    }

    public function testNoCategory()
    {
        $advert = $this->initAdvert();

        $this->assertEmpty($advert->getCategories());
    }

    public function testReturnImageClass()
    {
        $advert = new Advert;
        $advert->setImage(new \PlatformBundle\Entity\Image);

        $this->assertInstanceOf(\PlatformBundle\Entity\Image::class, $advert->getImage());
    }
}


