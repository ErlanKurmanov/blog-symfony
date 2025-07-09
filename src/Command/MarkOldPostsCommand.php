<?php

namespace App\Command;

use App\Repository\PostRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:mark-old-posts',
    description: 'Mark posts older than the 7 day as expired.',
)]
class MarkOldPostsCommand extends Command
{
    private PostRepository $postRepository;
    private EntityManagerInterface $entityManager;

    public function __construct(PostRepository $postRepository, EntityManagerInterface $entityManager)
    {
        parent::__construct();
        $this->postRepository = $postRepository;
        $this->entityManager = $entityManager;
    }

    protected function configure(): void
    {
        $this
            ->addArgument('arg1', InputArgument::OPTIONAL, 'Argument description')
            ->addOption('option1', null, InputOption::VALUE_NONE, 'Option description')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $oneWeekAgo = new \DateTimeImmutable('-7 days');

        $oldPosts = $this->postRepository->findOlderThan($oneWeekAgo);

        foreach ($oldPosts as $post) {
            $post->setIsExpired(true);
        }

        $this->entityManager->flush();

        $output->writeln(count($oldPosts) . ' posts marked as expired.');

        return Command::SUCCESS;
    }
}
