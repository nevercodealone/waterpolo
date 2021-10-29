<?php

namespace App\Service;

use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpClient\HttpClient;

/**
 *
 */
class NewsService
{
    /**
     * @var CacheItemPoolInterface
     */
    private CacheItemPoolInterface $cache;

    /**
     * @var ImageService
     */
    private ImageService $imageService;

    /**
     * @var Filesystem
     */
    private Filesystem $fileSystem;

    /**
     * @var string
     */
    private string $tmpImg = '/tmp/photos/waterpolo.jpg';

    public function __construct(CacheItemPoolInterface $cache, ImageService $imageService, Filesystem $fileSystem)
    {
        $this->cache = $cache;
        $this->imageService = $imageService;
        $this->fileSystem = $fileSystem;
    }

    /**
     * @return array<string>
     */
    public function getNews(): array
    {
        $cacheItem = $this->cache->getItem('content');

        if (!$cacheItem->isHit()) {
            $content = [];
        } else {
            $cacheContent = $cacheItem->get();
            $content = $cacheContent['news'];
        }

        return $content;
    }

    /**
     * @return array<string>
     */
    public function getNewsFromApi(): array
    {
        $client = HttpClient::create();
        $newsApiUrl = 'https://newsapi.org/v2/everything?q=wasserball&sortBy=publishedAt&language=de&apiKey=';
        $response = $client->request('GET', $newsApiUrl . $_ENV['NEWSAPI']);

        $response->getStatusCode();
        // $content = $response->getContent();
        $content = $response->toArray();
        $articles = $content['articles'];

        foreach ($articles as $key => $news) {
            if ('Sueddeutsche.de' === $news['source']['name']) {
                unset($articles[$key]);
                continue;
            }

            $this->storeTempFileWithImage($news);

            $webEntities = $this->imageService->getWebEntities($this->tmpImg);

            $isDetectedKeyword = $this->isDetected($webEntities);

            if (!$isDetectedKeyword) {
                unset($articles[$key]);
                continue;
            }

            $articles[$key]['webEntities'] = $webEntities;
        }

        return $articles;
    }

    /**
     * @param array<string> $article
     */
    private function storeTempFileWithImage(array $article): void
    {
        if (!$this->fileSystem->exists('tmp/photos')) {
            $this->fileSystem->mkdir('/tmp/photos', 0700);
        }

        $filename = $article['urlToImage'];

        $this->fileSystem->copy($filename, $this->tmpImg);
    }

    /**
     * @param array<string> $webEntities
     */
    private function isDetected(array $webEntities): bool
    {
        $keywords = [
            'water polo',
            'wasserball',
            'wasserfreunde',
            'swimming',
            'water polo cap',
            'marko stamm',
        ];

        foreach ($webEntities as $webEntity) {
            $webEntity = strtolower($webEntity);
            if (in_array($webEntity, $keywords)) {
                return true;
            }
        }

        return false;
    }
}
