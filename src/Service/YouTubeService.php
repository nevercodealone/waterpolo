<?php

namespace App\Service;

use Google_Service_YouTube;
use Psr\Cache\CacheItemPoolInterface;


/**
 *
 */
class YouTubeService
{
    public function __construct(private Google_Service_YouTube $youtubeService, private CacheItemPoolInterface $cache)
    {
    }


    /**
     * @return array<string>
     */
    public function getVideos(): array
    {
        $cacheItem = $this->cache->getItem('content');

        if (!$cacheItem->isHit()) {
            $content = [];
        } else {
            $cacheContent = $cacheItem->get();
            $content = $cacheContent['videos'];
        }

        return $content;
    }

    /**
     * @param array<string> $keywords
     * @return array<string,mixed>
     */
    public function getVideoByKeywordsFromApi(array $keywords): array
    {
        $videos = [];
        $dates = [];
        $counts = [];

        foreach ($keywords as $keyword) {
            $params = [
                'q' => $keyword,
                'order' => 'date',
                'maxResults' => 10,
            ];

            $videoList = $this->youtubeService->search->listSearch('snippet', $params);

            $videosFromApi = $videoList['items'];

            foreach ($videosFromApi as $key => $item) {
                $videosFromApi[$key]['keyword'] = $keyword;
            }

            $counts[$keyword] = count($videosFromApi);

            $videos = array_merge($videos, $videosFromApi);
        }

        foreach ($videos as $key => $item) {
            $videos[$key]['publishedAt'] = $item['snippet']['publishedAt'];
            $dates[] = $item['snippet']['publishedAt'];
        }

        array_multisort($dates, SORT_DESC, SORT_STRING, $videos);

        return [
            'videos' => $videos,
            'counts' => $counts,
        ];
    }
}
