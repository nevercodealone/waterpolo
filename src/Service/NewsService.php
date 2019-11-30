<?php

namespace App\Service;

use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\HttpClient\HttpClient;

class NewsService
{
    /**
     * @var CacheItemPoolInterface
     */
    private $cache;

    public function __construct(CacheItemPoolInterface $cache)
    {
        $this->cache = $cache;
    }

    public function getNews()
    {
        $cacheItem = $this->cache->getItem('news');

        if (!$cacheItem->isHit()) {
            $client = HttpClient::create();
            $response = $client->request('GET', 'https://newsapi.org/v2/everything?q=wasserball&from=2019-10-30&sortBy=publishedAt&language=de&apiKey=' . $_ENV['NEWSAPI']);

            $statusCode = $response->getStatusCode();
            $contentType = $response->getHeaders()['content-type'][0];
            $content = $response->getContent();
            $content = $response->toArray();

            foreach ($content['articles'] as $key => $article) {
                if ($article['source']['name'] === 'Sueddeutsche.de') {
                    unset($content['articles'][$key]);
                }
            }

            $cacheItem->set($content);
            $this->cache->save($cacheItem);
        }

        return $cacheItem->get();
    }
}
