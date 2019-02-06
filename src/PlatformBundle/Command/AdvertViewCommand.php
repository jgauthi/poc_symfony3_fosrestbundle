<?php
namespace PlatformBundle\Command;

use Doctrine\ORM\EntityManagerInterface;
use PlatformBundle\Entity\Advert;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\{InputArgument, InputInterface};
use Symfony\Component\Console\Output\OutputInterface;

class AdvertViewCommand extends Command
{
    protected static $defaultName = 'advert:view';

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager, ?string $name = null)
    {
        parent::__construct($name);
        $this->entityManager = $entityManager;
    }

    protected function configure(): void
    {
        $this->setDescription('Advert View item.')
            ->setHelp('This command allows you to look advert item.')
        ;

        $this->addArgument('id', InputArgument::REQUIRED, 'View Advert by ID');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|void|null
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        $blogPostId = $input->getArgument('id');

        $blogPostRepository = $this->entityManager->getRepository(Advert::class);
        $blogPost = $blogPostRepository->find($blogPostId);

        if(empty($blogPost)) {
            die($output->writeln('<error>Advert not found.</error>'));
        }

        $attach = [];
        if(!empty($blogPost->getApplications()->count())) {
            $attach[] = $blogPost->getApplications()->count().' applications';
        }
        if(!empty($blogPost->getCategories()->count())) {
            $categories = [];
            foreach ($blogPost->getCategories() as $category) {
                $categories[] = $category->getName();
            }
            $attach[] = 'Categories: '. implode(', ', $categories);
        }

        $output->writeln([
            $blogPost->getTitle(),
            'By: '. $blogPost->getAuthor().
            ' on '.$blogPost->getDate()->format('Y-m-d H:i'),
            'Attach: '. implode(', ', $attach),
            '',
            ''. $blogPost->getContent(),
        ]);
    }
}
