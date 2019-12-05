<?php
namespace App\Service;

use Psr\Cache\CacheItemPoolInterface;

class YouTubeService
{
    /** @var \Google_Service_YouTube */
    private $youtubeService;

    /** @var CacheItemPoolInterface */
    private $cache;

    public function __construct(\Google_Service_YouTube $youtubeService, CacheItemPoolInterface $cache)
    {
        $this->youtubeService = $youtubeService;
        $this->cache = $cache;
    }

    public function getItemsFromChannel(): array
    {
        $params = [
            'maxResults' => 10,
            'playlistId' => 'PLf9rhfhnyGJ-l6WlrMdy-0fZNcSEqjb4y'
        ];

        $cacheItem = $this->cache->getItem('videos');

        if (!$cacheItem->isHit()) {
            $videoList = $this->playlistItemsListByPlaylistId('snippet',$params);
            $videos = array_reverse($videoList['items']);
            $videos = array_slice($videos, 0, 9);

            $cacheItem->set($videos);
            $this->cache->save($cacheItem);
        }

        return $cacheItem->get();

    }

    private function playlistItemsListByPlaylistId(string $part, array $params): \Google_Service_YouTube_PlaylistItemListResponse
    {
        $params = array_filter($params);

        return $this->youtubeService->playlistItems->listPlaylistItems(
            $part,
            $params
        );
    }
}
