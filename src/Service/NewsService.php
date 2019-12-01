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

                ## Store tmp image
                if (!$this->fileSystem->exists("tmp/photos")) {
                    $this->fileSystem->mkdir('/tmp/photos', 0700);
                }

                $filename = $article['urlToImage'];

                $this->fileSystem->copy($filename, 'waterpolo.jpg');


                $webEntities = $this->imageService->getWebEntities('waterpolo.jpg');

            }

            $cacheItem->set($content);
            $this->cache->save($cacheItem);
        }

        return $cacheItem->get();
    }
}
