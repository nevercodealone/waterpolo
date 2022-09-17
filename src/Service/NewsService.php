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
     * @var string
     */
    private string $tmpImg = '/tmp/photos/waterpolo.jpg';

    public function __construct(private CacheItemPoolInterface $cacheItemPool, private Filesystem $fileSystem)
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
