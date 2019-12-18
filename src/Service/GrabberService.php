<?php


namespace App\Service;


use phpDocumentor\Reflection\Types\Mixed_;

class GrabberService
{
    public function getItems(): array
    {
        $url = 'https://waspo98.de/feed/';
        $content = file_get_contents($url);
        $xml = simplexml_load_string($content);
        $json = json_encode($xml);
        $news = json_decode($json,true)['channel']['item'];

        foreach ($news as $key => $item) {
            $image = $this->getImageFromUrl($item['guid']);

            if(!$image) {
                unset($news[$key]);
                continue;
            }

            $news[$key]['image'] = $image;
        }

        return $news;
    }

    private function getImageFromUrl($url)
    {
        $imageBlackList = [
            'logo-neu.jpg',
            'youtube.png',
            'instagram.png',
            'facebook.png'
        ];

        $contentImage = false;
        $content = file_get_contents($url);
        libxml_use_internal_errors(true);
        $dom = new \DOMDocument();
        $dom->loadHTML($content);
        libxml_use_internal_errors(false);
        $images = $dom->getElementsByTagName('img');

        foreach ($images as $image) {
            $src = $image->getAttribute('src');

            foreach ($imageBlackList as $needle) {
                if (strpos($src, $needle) !== false) {
                    continue 2;
                }
            }
            $contentImage = $src;
        }

        return $contentImage;
    }
}
