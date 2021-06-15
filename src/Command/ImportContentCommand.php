<?php

namespace App\Command;

use App\Service\GrabberService;
use App\Service\NewsService;
use App\Service\YouTubeService;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ImportContentCommand extends Command
{
    protected static $defaultName = 'app:import:content';

    /**
     * @var bool
     */
    private $debug;

    /** @var CacheItemPoolInterface */
    private $cache;

    /** @var NewsService */
    private $newsService;

    /** @var YouTubeService */
    private $youTubeService;

    /** @var GrabberService */
    private $grabberService;

    public function __construct(
        YouTubeService $youTubeService,
        NewsService $newsService,
        CacheItemPoolInterface $cache,
        GrabberService $grabberService,
        bool $debug = false
    ) {
        $this->cache = $cache;
        $this->newsService = $newsService;
        $this->youTubeService = $youTubeService;
        $this->grabberService = $grabberService;
        $this->debug = $debug;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription('Import from APIs')
            ->addArgument(
                'debug',
                InputArgument::OPTIONAL,
                'Only run firstDomain'
            )
            ->setHelp('This command will get all new content, clear the cache and fill it')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $debug = $input->getArgument('debug');

        $content = [];

        $output->writeln([
            'Start job.',
        ]);

        $output->writeln([
            'Import NEWS process',
            '============',
            '',
        ]);

        $content['news'] = $this->grabberService->getItems($debug);

        $output->writeln([
            'Count news: ' . count($content['news']['news']),
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
            'Count:'.count($content['videos']['videos']),
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

        $cacheItem = $this->cache->getItem('content');

        $cacheItem->set($content);
        $this->cache->save($cacheItem);

        $output->writeln([
            'End job.',
        ]);

        return 0;
    }
}
