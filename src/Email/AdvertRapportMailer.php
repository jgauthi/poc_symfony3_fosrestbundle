<?php
namespace App\Email;

use App\DataFixtures\AdvertFixtures;
use App\Entity\Advert;
use App\Entity\Application;
use Swift_Message;
use Symfony\Component\Validator\Exception\InvalidArgumentException;

class AdvertRapportMailer extends AbstractMailer
{
    /**
     * @param string $to
     * @param Advert $advert
     * @return int|null
     */
    public function send(string $to, Advert $advert): ?int
    {
        if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException('Email incorrect');
        }

        // Declare Advert Data
        $attach = [];
        if (!empty($advert->getCategories()->count())) {
            $categories = [];
            foreach ($advert->getCategories() as $category) {
                $categories[] = $category->getName();
            }
            $attach[] = 'Categories: '.implode(', ', $categories);
        }

        $content = [
            $advert->getTitle(),
            'By: '.$advert->getAuthor().
            ' on '.$advert->getDate()->format('Y-m-d H:i'),
            'Attach: '.implode(', ', $attach),
            '',
            ''.$advert->getContent(),
        ];

        $applications = $advert->getApplications();
        if (!empty($applications)) {
            $content[] = '';
            $content[] = 'Applications: ';

            /** @var Application $app */
            foreach ($applications->toArray() as $app) {
                $content[] = "- {$app->getAuthor()} ({$app->getCity()}): {$app->getContent()}";
            }
        }

        $message = new Swift_Message(
            sprintf('Advert Rapport "%s"', $advert->getTitle()),
            implode("\n", $content)
        );

        $message
            ->addTo($to)
            ->addFrom('admin@'.AdvertFixtures::MAIL_DOMAIN)
        ;

        return $this->mailer->send($message);
    }
}
