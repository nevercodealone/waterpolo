<?php

namespace App\Tests\unit\Service;

use Google_Service_YouTube;
use App\Service\YouTubeService;
use PHPUnit\Framework\TestCase;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\Cache\CacheItem;

class YouTubeServiceTest extends TestCase
{
    private $googleServiceYoutube;

    protected function setUp(): void
    {
        parent::setUp();

        $this->googleServiceYoutube = $this->createMock(Google_Service_YouTube::class);
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

    public function testGetVideosReturnsNonEmptyArrayWhenCacheItemIsTrue(): void
    {
        $item = $this->createMock(CacheItemInterface::class);
        $item->expects($this->once())
            ->method('isHit')
            ->willReturn(true);

        $item->expects($this->once())
            ->method('get')
            ->willReturn([
                'videos' => ['unit' => 'video', 'john' => 'doe']
            ]);


        $cacheItemPoolInterface = $this->createMock(CacheItemPoolInterface::class);
        $cacheItemPoolInterface->expects($this->once())
            ->method('getItem')
            ->willReturn($item);

        $youTubeService = new YouTubeService($this->googleServiceYoutube, $cacheItemPoolInterface);

        $videos = $youTubeService->getVideos();

        self::assertCount(2, $videos);
        self::assertSame('video',$videos['unit']);
        self::assertSame('doe',$videos['john']);
    }
}
