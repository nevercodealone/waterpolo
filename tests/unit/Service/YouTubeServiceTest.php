<?php

namespace App\Tests\unit\Service;

use App\Service\YouTubeService;
use PHPUnit\Framework\TestCase;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;

class YouTubeServiceTest extends TestCase
{
    private $googleServiceYoutube;

    private $cacheItemPoolInterface;

    protected function setUp(): void
    {
        parent::setUp();

        $this->googleServiceYoutube = $this->createMock(\Google_Service_YouTube::class);
        $this->cacheItemPoolInterface = $this->createMock(CacheItemPoolInterface::class);
    }

    public function testGetVideosReturnEmptyArrayWhenCacheItemIsFalse()
    {
        $item = $this->createMock(CacheItemInterface::class);
        $item->expects($this->once())
            ->method('isHit')
            ->willReturn(false);

        $cacheItemPoolInterface = $this->createMock(CacheItemPoolInterface::class);
        $cacheItemPoolInterface->expects($this->once())
            ->method('getItem')
            ->willReturn($item);

        $youTubeService = new YouTubeService($this->googleServiceYoutube, $cacheItemPoolInterface);

        $this->assertEmpty($youTubeService->getVideos(), 'Return of get videos');
    }
}
