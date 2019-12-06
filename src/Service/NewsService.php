<?php

namespace App\Service;

use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpClient\HttpClient;

class NewsService
{
    /**
     * @var CacheItemPoolInterface
     */
    private $cache;

    /**
     * @var ImageService
     */
    private $imageService;

    /**
     * @var Filesystem
     */
    private $fileSystem;

    private $tmpImg = '/tmp/photos/waterpolo.jpg';

    public function __construct(CacheItemPoolInterface $cache, ImageService $imageService, Filesystem $fileSystem)
    {
        $this->cache = $cache;
        $this->imageService = $imageService;
        $this->fileSystem = $fileSystem;
    }

    public function getNews()
    {
        $cacheItem = $this->cache->getItem('news');

        if (!$cacheItem->isHit()) {
            $content = [];
        } else {
            $cacheContent = $cacheItem->get();
            $content = $cacheContent['news'];
        }

        return $content;
    }

    public function getNewsFromApi(): array
    {
        $client = HttpClient::create();
        $response = $client->request('GET', 'https://newsapi.org/v2/everything?q=wasserball&sortBy=publishedAt&language=de&apiKey=' . $_ENV['NEWSAPI']);

        $statusCode = $response->getStatusCode();
        $contentType = $response->getHeaders()['content-type'][0];
        $content = $response->getContent();
        $content = $response->toArray();
        $articles = $content['articles'];

        foreach ($articles as $key => $news) {
            if ($news['source']['name'] === 'Sueddeutsche.de') {
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
     * @param $article
     */
    private function storeTempFileWithImage($article): void
    {
        if (!$this->fileSystem->exists("tmp/photos")) {
            $this->fileSystem->mkdir('/tmp/photos', 0700);
        }

        $filename = $article['urlToImage'];

        $this->fileSystem->copy($filename, $this->tmpImg);
    }

    private function isDetected(array $webEntities): bool
    {
        $keywords = [
            'water polo',
            'wasserball',
            'wasserfreunde',
            'swimming',
            'water polo cap',
            'marko stamm'
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
