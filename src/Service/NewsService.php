<?php

namespace App\Service;

use Psr\Cache\CacheItemPoolInterface;

class NewsService
{
    public function __construct(private readonly CacheItemPoolInterface $cacheItemPool)
    {
    }

    /**
     * @return array<string>
     */
    public function getNews(): array
    {
        $cacheItem = $this->cacheItemPool->getItem('content');

        if (!$cacheItem->isHit()) {
            $content = [];
        } else {
            $cacheContent = $cacheItem->get();
            $content = $cacheContent['news'];
        }

        return $content;
    }
}
