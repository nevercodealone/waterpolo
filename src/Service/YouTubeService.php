<?php

namespace App\Service;

use Google_Service_YouTube;
use Psr\Cache\CacheItemPoolInterface;

class YouTubeService
{
    public function __construct(private readonly Google_Service_YouTube $googleServiceYouTube, private readonly CacheItemPoolInterface $cacheItemPool)
    {
    }

    /**
     * @return array<string>
     */
    public function getVideos(): array
    {
        $cacheItem = $this->cacheItemPool->getItem('content');

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
     *
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

            $videoList = $this->googleServiceYouTube->search->listSearch('snippet', $params);

            $videosFromApi = $videoList['items'];

            foreach ($videosFromApi as $key => $item) {
                $videosFromApi[$key]['keyword'] = $keyword;
            }

            $counts[$keyword] = is_countable($videosFromApi) ? count($videosFromApi) : 0;

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
