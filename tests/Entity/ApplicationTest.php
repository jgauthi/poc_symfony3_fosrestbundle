<?php
namespace App\Tests\Entity;

use App\Entity\{Application, User};
use PHPUnit\Framework\TestCase;

class ApplicationTest extends TestCase
{
    private function initApplication(): Application
    {
        $user = new User();
        $user->setUsername('tyrion-lannister');

        $application = new Application();
        $application->setAuthor($user)
            ->setContent('MyTestContent')
            ->setDate(\DateTime::createFromFormat('Y-m-d', '2018-07-15'))
            ->setCity('Port-Réal')
            ->setSalaryClaim(5000);

        return $application;
    }

    public function testApplicationHasAdvertAttribute(): void
    {
        $this->assertClassHasAttribute('advert', application::class);
    }

    /**
     * @throws \Exception
     */
    public function testEqualDatetimeVar(): void
    {
        $application = $this->initApplication();
        $date = new \DateTime();
        $date->setDate(2018, 7, 15);

        $this->assertSame($date->format('Y-m-d'), $application->getDate()->format('Y-m-d'));
    }

    /**
     * @dataProvider dataSalaryExpected
     *
     * @param $salary
     * @param $experted
     */
    public function testSeveralSalary($salary, $experted): void
    {
        $application = new Application();
        $application->setSalaryClaim($salary);

        $this->assertLessThan($experted, $application->getSalaryClaim());
    }

    /**
     * @return array
     */
    public function dataSalaryExpected(): array
    {
        // salary claim, maximum expected
        return [
            [2200, 2500],
            [1500, 1800],
            [3200, 3300],
        ];
    }
}
