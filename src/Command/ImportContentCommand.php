<?php


namespace App\Command;

use App\Service\GrabberService;
use App\Service\NewsService;
use App\Service\YouTubeService;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ImportContentCommand extends Command
{
    protected static $defaultName = 'app:import:content';

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
        GrabberService $grabberService
    ) {
        $this->cache = $cache;
        $this->newsService = $newsService;
        $this->youTubeService = $youTubeService;
        $this->grabberService = $grabberService;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription('Import from APIs')
            ->setHelp('This command will get all new content, clear the cache and fill it');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
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
            'Count:' . count($content['news']['news']),
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
            'Count:' . count($content['videos']['videos']),
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

        if ($this->cache->getItem('content')) {
            $this->cache->deleteItem('content');
        }
        $cacheItem = $this->cache->getItem('content');

        $cacheItem->set($content);
        $this->cache->save($cacheItem);

        $output->writeln([
            'End job.',
        ]);

        return 0;
    }
}
