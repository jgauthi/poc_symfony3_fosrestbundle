<?php
namespace App\Command;

use App\Entity\Advert;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\{InputInterface, InputOption};
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class AdvertListCommand extends Command
{
    protected static $defaultName = 'advert:list';

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
        $this->setDescription('Advert List content.')
            ->setHelp('This command allows you to list advert items.')
        ;

        $this
            ->addOption('number', 'nb', InputOption::VALUE_REQUIRED, 'How many advert appear ?', 10)
            ->addOption('order', null, InputOption::VALUE_REQUIRED, 'Which colors do you like?', 'DESC')
        ;
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
        $io = new SymfonyStyle($input, $output);
        $io->title('Advert List');

        $limit = ((is_numeric($input->getOption('number')) ? $input->getOption('number') : 10));
        $order = (('ASC' === $input->getOption('order')) ? 'ASC' : 'DESC');

        $blogPostRepository = $this->entityManager->getRepository(Advert::class);
        $blogPostList = $blogPostRepository->findBy([], ['date' => $order], $limit);

        if (empty($blogPostList)) {
            $io->caution('No Advert');
            die();
        }

        $titles = ['ID', 'Title', 'Author', 'Date', 'Published', 'Content'];
        $rows = [];

        foreach ($blogPostList as $blogPost) {
            $content = $blogPost->getContent();
            if (mb_strlen($content) > 50) {
                $content = trim(mb_substr($content, 0, 50)).'...';
            }

            $rows[] = [
                $blogPost->getId(),
                $blogPost->getTitle(),
                $blogPost->getAuthor(),
                $blogPost->getDate()->format('Y-m-d'),
                (($blogPost->getPublished()) ? 'Yes' : 'No'),
                $content,
            ];
        }

        $io->table($titles, $rows);
    }
}
