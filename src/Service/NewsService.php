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
        $keywords = [
            'water polo',
            'wasserball',
            'wasserfreunde',
            'swimming',
            'water polo cap'
        ];

        $cacheItem = $this->cache->getItem('news');

        if (!$cacheItem->isHit()) {
            $client = HttpClient::create();
            $response = $client->request('GET', 'https://newsapi.org/v2/everything?q=wasserball&sortBy=publishedAt&language=de&apiKey=' . $_ENV['NEWSAPI']);

            $statusCode = $response->getStatusCode();
            $contentType = $response->getHeaders()['content-type'][0];
            $content = $response->getContent();
            $content = $response->toArray();

            foreach ($content['articles'] as $key => $article) {
                if ($article['source']['name'] === 'Sueddeutsche.de') {
                    unset($content['articles'][$key]);
                    continue;
                }

                $this->storeTempFileWithImage($article);

                $webEntities = $this->imageService->getWebEntities($this->tmpImg);

                $isDetectedKeyword = false;
                foreach ($webEntities as $webEntity) {
                    foreach ($keywords as $keyword) {
                        if (strpos(strtolower($webEntity), $keyword)) {
                            $content['articles'][$key]['webEntities'] = $webEntities;
                            $isDetectedKeyword = true;
                            break;
                        }
                    }
                }

                if(!$isDetectedKeyword) {
                    unset($content['articles'][$key]);
                    continue;
                }

            }

            $cacheItem->set($content);
            $this->cache->save($cacheItem);
        }

        return $cacheItem->get();
    }

    /**
     * @param $article
     */
    private function storeTempFileWithImage($article): void
    {
## Store tmp image
        if (!$this->fileSystem->exists("tmp/photos")) {
            $this->fileSystem->mkdir('/tmp/photos', 0700);
        }

        $filename = $article['urlToImage'];

        $this->fileSystem->copy($filename, $this->tmpImg);
    }
}
