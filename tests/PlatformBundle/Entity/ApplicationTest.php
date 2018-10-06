<?php
namespace Tests\PlatformBundle\Entity;

use PlatformBundle\Entity\Application;
use PHPUnit\Framework\TestCase;

class ApplicationTest extends TestCase
{
    private function initApplication()
    {
        $application = new Application;

        $application->setAuthor('Tyrion Lannister')
            ->setContent('MyTestContent')
            ->setDate( \DateTime::createFromFormat('Y-m-d', '2018-07-15') )
            ->setCity('Port-Réal ')
            ->setSalaryClaim(5000);

        return $application;
    }

   public function testApplicationHasAdvertAttribute()
   {
       $this->assertClassHasAttribute('advert', application::class);
   }


   public function testEqualDatetimeVar()
   {
       $application = $this->initApplication();
       $date = new \DateTime;
       $date->setDate(2018, 7, 15);

       $this->assertEquals($date->format('Y-m-d'), $application->getDate()->format('Y-m-d'));
   }

    /**
     * @dataProvider dataSalaryExpected
     */
    public function testSeveralSalary($salary, $experted)
    {
        $application = new Application;
        $application->setSalaryClaim($salary);

        $this->assertLessThan($experted, $application->getSalaryClaim());
    }

    public function dataSalaryExpected()
    {
        // salary claim, maximum expected
        return [
            [2200, 2500],
            [1500, 1800],
            [3200, 3300]
        ];
    }
}


