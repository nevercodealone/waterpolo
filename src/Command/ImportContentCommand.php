<?php


namespace App\Command;


use App\Service\NewsService;
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

    public function __construct(NewsService $newsService, CacheItemPoolInterface $cache)
    {
        $this->cache = $cache;
        $this->newsService = $newsService;

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
        $output->writeln([
            'Import NEWS process',
            '============',
            '',
        ]);


        $output->writeln(['Import NEWS process',
            '============',
            '',
        ]);
        $content = [];
        $content['news'] = $this->newsService->getNewsFromApi();

        $output->writeln([
            'Count:' . count($content['news']),
            '============',
            '',
        ]);

        $output->writeln([
            'End import NEWS',
            '============',
            '',
        ]);

        $output->writeln([
            'Cache handling',
        ]);

        if ($this->cache->getItem('news')) {
            $this->cache->deleteItem('news');
        }

        $cacheItem = $this->cache->getItem('news');
        $cacheItem->set($content);
        $this->cache->save($cacheItem);

        $output->writeln([
            'End job.',
        ]);

        return 0;
    }
}
