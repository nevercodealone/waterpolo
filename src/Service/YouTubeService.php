<?php
namespace App\Service;

class YouTubeService
{
    /** @var \Google_Service_YouTube */
    private $youtubeService;

    public function __construct(\Google_Service_YouTube $youtubeService)
    {
        $this->youtubeService = $youtubeService;
    }

    public function getItemsFromChannel(): array
    {
        $params = [
            'maxResults' => 10,
            'playlistId' => 'PLf9rhfhnyGJ-l6WlrMdy-0fZNcSEqjb4y'
        ];

        $videoList = $this->playlistItemsListByPlaylistId('snippet',$params);
        $videos = array_reverse($videoList['items']);
        $videos = array_slice($videos, 0, 9);

        return $videos;
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
