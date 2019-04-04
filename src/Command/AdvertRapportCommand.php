<?php

namespace App\Command;

use App\DataFixtures\AdvertFixtures;
use App\Email\AdvertRapportMailer;
use App\Entity\Advert;
use App\Utils\Text;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\{InputArgument, InputInterface};
use Symfony\Component\Console\Output\OutputInterface;

class AdvertRapportCommand extends Command
{
    protected static $defaultName = 'advert:rapport';

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;
    /**
     * @var AdvertRapportMailer
     */
    private $mailer;

    /**
     * AdvertRapportCommand constructor.
     * @param EntityManagerInterface $entityManager
     * @param AdvertRapportMailer $mailer
     * @param string|null $name
     */
    public function __construct(EntityManagerInterface $entityManager, AdvertRapportMailer $mailer, ?string $name = null)
    {
        parent::__construct($name);
        $this->entityManager = $entityManager;
        $this->mailer = $mailer;
    }

    protected function configure(): void
    {
        $this->setDescription('Advert Rapport item.')
            ->setHelp('This command allows you to send rapport advert by mail.')
        ;

        $this->addArgument('id', InputArgument::REQUIRED, 'Get Advert by ID');
        $this->addArgument('email', InputArgument::OPTIONAL, 'To email');
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @throws \Exception
     *
     * @return int|void|null
     */
    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        $advertId = $input->getArgument('id');
        $to = $input->getArgument('email');

        $advertRepository = $this->entityManager->getRepository(Advert::class);
        $advert = $advertRepository->find($advertId);

        if (empty($advert)) {
            die($output->writeln('<error>Advert not found.</error>'));
        }

        if(empty($to)) {
            $to = $advert->getAuthor()->get;
        }

//        if ($this->mailer->send($to, $advert)) {
//            $output->writeln("<fg=green>Rapport successfully to $to</>");
//        }
//        else $output->writeln("<error>Fail sending rapport to $to</error>");

        $this->mailer->send($to, $advert); //todo: Détecter si le mail est correctement envoyé
        $output->writeln("<fg=green>Rapport successfully to $to</>");
    }
}
