<?php

namespace App\Grabber;

use App\Handler\ImageHandler;
use Symfony\Component\DomCrawler\Crawler;

class WebsiteGrabber
{
    public function __construct(
        private ImageHandler $imageHandler
    )
    {}

    /**
     * @return array<array>
     * @throws \Exception
     */
    public function getNewsItemsFromUrl(string $url, array $properties): array
    {
        $newsFeed = [];
        if($properties['domain'] === 'www.deutsche-wasserball-liga.de') {
            $content = file_get_contents($url.'/feed/');
            $xml = simplexml_load_string($content);
            $json = json_encode($xml, JSON_THROW_ON_ERROR);
            $newsFeed = json_decode($json, true, 512, JSON_THROW_ON_ERROR)['channel']['item'];
        }

        $content = file_get_contents($url);
        $crawler = new Crawler($content);

        $this->removeContentRelations($url, $crawler);
        $images = $crawler->filter($properties['image']);
        $titles = $crawler->filter($properties['title']);
        $links = $crawler->filter($properties['more-link']);

        $news = [];
        foreach ($titles as $key => $title) {
            $title = $title->textContent;
            $image = $images->eq($key)->attr('src');
            $imageUrl = $image;
            if($properties['domain'] === 'homepage.svl08.com') {
                $image = str_replace('..', '', $image);
                $imageUrl = $url . $image;
            }

            if (!$image) {
                unset($news[$key]);
                continue;
            }

            $filename = $this->imageHandler->saveFileFromUrl($imageUrl);

            if ($filename === '') {
                unset($news[$key]);
                continue;
            }

            $link = $url . '/' . $links->eq($key)->attr('href');

            if ($properties['domain'] === 'homepage.svl08.com') {
                $pubDate = $this->imageHandler->getdateFromImage($imageUrl);
            } else {
                $feedKey = array_search($title, array_column($newsFeed, 'title'), true);
                $pubDate = $newsFeed[$feedKey]['pubDate'];
            }

            $newsProperties = [
                'title' => $title,
                'image' => $filename,
                'link' => $link,
                'url' => $properties['domain'],
                'pubDate' => $pubDate,
            ];
            $news[] = $newsProperties;
        }


        return $news;
    }

    private function removeContentRelations(string $url, Crawler $crawler): void
    {
        if (str_contains($url, 'deutsche-wasserball-liga.de')) {
            $crawler->filter('#carousel-eyecatcher')->each(function (Crawler $crawler) {
                $node = $crawler->getNode(0);
                $node->parentNode->removeChild($node);
            });

            $crawler->filter('.content-header')->each(function (Crawler $crawler) {
                $node = $crawler->getNode(0);
                $node->parentNode->removeChild($node);
            });
        }
    }
}
