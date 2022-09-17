<?php

namespace App\Grabber;

class WordpressGrabber
{
    /**
     * @return array<int, array>
     */
    public function getItemsFromFeedUrl(string $url): array
    {
        $news = [];
        $content = file_get_contents($url);
        if ($content) {
            $xml = simplexml_load_string($content, 'SimpleXMLElement', LIBXML_NOCDATA);
            $json = json_encode($xml, JSON_THROW_ON_ERROR);
            $news = json_decode($json, true, 512, JSON_THROW_ON_ERROR)['channel']['item'];
            $news = array_slice($news, 0, 6);
        }

        return $news;
    }
}
