<?php

namespace App\Command;

use App\Service\GrabberService;
use App\Service\YouTubeService;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ImportContentCommand extends Command
{
    protected static $defaultName = 'app:import:content';

    public function __construct(
        private readonly YouTubeService $youTubeService,
        private readonly CacheItemPoolInterface $cacheItemPool,
        private readonly GrabberService $grabberService
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Import from APIs')
            ->addArgument(
                'debug',
                InputArgument::OPTIONAL,
                'Only run firstDomain'
            )
            ->setHelp('This command will get all new content, clear the cache and fill it');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $input->getArgument('debug') ?: null;

        $content = [];

        $output->writeln([
            'Start job.',
        ]);

        $output->writeln([
            'Import NEWS process',
            '============',
            '',
        ]);

        $content['news'] = $this->grabberService->getItems();

        $output->writeln([
            'Count news: '.count($content['news']['news']),
            '============',
            '',
        ]);

        $output->writeln([
            'End import NEWS',
            '============',
            '',
        ]);

        $output->writeln([
            'Import VIDEOS process',
            '============',
            '',
        ]);

        $content['videos'] = $this->youTubeService->getVideoByKeywordsFromApi(['wasserball', 'waterpolo']);

        $output->writeln([
            'Count:'.(is_countable($content['videos']['videos']) ? count($content['videos']['videos']) : 0),
            '============',
            '',
        ]);

        $output->writeln([
            'End import VIDEOS',
            '============',
            '',
        ]);

        $output->writeln([
            'Cache handling',
        ]);

        $cacheItem = $this->cacheItemPool->getItem('content');

        $cacheItem->set($content);
        $this->cacheItemPool->save($cacheItem);

        $output->writeln([
            'End job.',
        ]);

        return 0;
    }
}
