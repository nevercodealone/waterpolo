<?php

namespace App\Grabber;

use App\Handler\ImageHandler;
use JsonException;
use Symfony\Component\DomCrawler\Crawler;

class WebsiteGrabber implements WebsiteGrabberInterface
{
    public function __construct(
        private readonly ImageHandler $imageHandler
    ) {
    }

    /**
     * @param array<string, string> $properties
     *
     * @return array<array>|false
     *
     * @throws JsonException
     */
    public function getNewsItemsFromUrl(string $url, array $properties): array|false
    {
        $content = file_get_contents($url);
        if (!$content) {
            return false;
        }

        $crawler = new Crawler($content);

        $newsFeed = [];
        if ('www.deutsche-wasserball-liga.de' === $properties['domain']) {
            $contentFeed = file_get_contents($url.'/feed/');
            if ($contentFeed) {
                $xml = simplexml_load_string($contentFeed);
                $json = json_encode($xml, JSON_THROW_ON_ERROR);
                $newsFeed = json_decode($json, true, 512, JSON_THROW_ON_ERROR)['channel']['item'];
            }
        }

        $this->removeContentRelations($url, $crawler);
        $images = $crawler->filter($properties['image']);
        $titles = $crawler->filter($properties['title']);
        $links = $crawler->filter($properties['more-link']);

        $news = [];
        foreach ($titles as $key => $title) {
            $title = $title->textContent;
            $image = $images->eq($key)->attr('src');

            if (!$image) {
                unset($news[$key]);
                continue;
            }

            $imageUrl = $image;
            if ('homepage.svl08.com' === $properties['domain']) {
                $image = str_replace('..', '', $image);
                $imageUrl = $url.$image;
            }

            $filename = $this->imageHandler->saveFileFromUrl($imageUrl);

            if ('' === $filename) {
                unset($news[$key]);
                continue;
            }

            $link = $url.'/'.$links->eq($key)->attr('href');

            if ('homepage.svl08.com' === $properties['domain']) {
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
                $domNode = $crawler->getNode(0);
                if (null === $domNode) {
                    return;
                }
                if (!$domNode->hasChildNodes()) {
                    return;
                }
                $domNode->parentNode->removeChild($domNode);
            });

            $crawler->filter('.content-header')->each(function (Crawler $crawler) {
                $domNode = $crawler->getNode(0);
                if (null !== $domNode) {
                    $domNode->parentNode->removeChild($domNode);
                }
            });
        }
    }
}
