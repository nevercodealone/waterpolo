<?php

namespace App\Service;

use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\KernelInterface;

class GrabberService
{
    /** @var Filesystem */
    private $fileSystem;

    /** @var KernelInterface */
    private $appKernel;

    private $tmpFolder;

    private $sourceDomains = [
        'h2o-polo.de' => [],
        'deutsche-wasserball-liga.de' => [],
        'ssv-esslingen.de' => ['category'],
        'wasserballecke.de' => [],
        'total-waterpolo.com' => [],
        'spandau04.de' => ['category'],
        'waspo98.de' => [],
        'www.dance.hr' => [],
    ];

    public function __construct(KernelInterface $appKernel, Filesystem $fileSystem)
    {
        $this->appKernel = $appKernel;
        $this->fileSystem = $fileSystem;

        $this->tmpFolder = $this->appKernel->getProjectDir().'/public/tmp/photos/';

        if (!$this->fileSystem->exists($this->tmpFolder)) {
            $this->fileSystem->mkdir($this->tmpFolder, 0777);
        }
    }

    public function getItems($debug = false): array
    {
        if ('firstDomain' === $debug) {
            $this->sourceDomains = array_slice($this->sourceDomains, 0, 1);
        }

        $allNews = [];
        foreach ($this->sourceDomains as $sourceDomain => $specials) {
            $protocol = 'https';

            if (isset($specials) && in_array('http', $specials)) {
                $protocol = 'http';
            }

            if ('deutsche-wasserball-liga.de' === $sourceDomain) {
                $news = $this->getNewsItemsFromUrl($protocol.'://www.'.$sourceDomain);
            } else {
                $feedUrl = $protocol.'://'.$sourceDomain.'/feed/';
                try {
                    $content = file_get_contents($feedUrl);
                    $xml = simplexml_load_string($content, 'SimpleXMLElement', LIBXML_NOCDATA);
                    $json = json_encode($xml, JSON_THROW_ON_ERROR);
                    $news = json_decode($json, true, 512, JSON_THROW_ON_ERROR)['channel']['item'];
                    $news = array_slice($news, 0, 6);

                    foreach ($news as $key => $item) {
                        if (isset($specials) && in_array('category', $specials, true)) {
                            if (!isset($item['category']) || !is_array($item['category'])) {
                                unset($news[$key]);
                                continue;
                            }

                            $category = array_map('strtolower', $item['category']);
                            if (!in_array('wasserball', $category)) {
                                unset($news[$key]);
                                continue;
                            }
                        }

                        $url = $item['guid'];
                        $image = $this->getImageFromUrl($url);

                        if (!$image) {
                            unset($news[$key]);
                            continue;
                        }

                        $filename = basename($image);
                        $this->fileSystem->copy($image, $this->tmpFolder.$filename);

                        $news[$key]['image'] = $filename;
                        $news[$key]['url'] = $sourceDomain;
                    }

                    $allNews = [...$allNews, ...$news];
                } catch (\Exception $exception) {
                    $msg = $feedUrl.'|'.$exception->getMessage();
                    print_r($msg);
                }
            }
        }

        usort($allNews, function ($a, $b) {
            $actual = strtotime($a['pubDate']);
            $next = strtotime($b['pubDate']);

            return $actual - $next;
        });

        $array_reverse = array_reverse($allNews);

        return [
            'news' => $array_reverse,
            'sourceDomains' => $this->sourceDomains,
        ];
    }

    private function getImageFromUrl($url)
    {
        $imageBlackListWaspo = [
            'logo-neu.jpg',
            'youtube.png',
            'instagram.png',
            'facebook.png',
        ];

        $imageBlackListSpandau = [
            'logo-spandau',
            '80x80',
            'plugins',
        ];

        $imageBlackListTotalWaterpolo = [
            'facebook.com',
            'w3.org',
            'water-polo-community.png',
            'Screen-Shot',
            'Award-Badge',
        ];

        $imageBlackListWasserballecke = [
            'wasserballecke_',
            'banner',
            'gravatar',
            '.gif',
            'image3',
            'ios_splasscreen',
            'appack',
            'googleplay',
            'data:image',
            'IMG_2165-1200x480.jpg', // Sharks logo
        ];

        $imageBlackListSsvEsslingen = [
            'logo.png',
        ];

        $imageBlackListDeutscheWasserballLiga = [
            'sportmuck',
            'logo',
        ];

        $imageBlackListProRecco = [
            'lutto.jpg',
            'WA0005',
            'contattaci.jpg',
            'INTELS.png',
            'turbo.png',
            'tossini.png',
            'video_prorecco.jpg',
        ];

        $imageBlackListDanceHr = [
            'grb-udruga-opt.png',
        ];

        $imageBlackList = array_merge(
            $imageBlackListWaspo,
            $imageBlackListSpandau,
            $imageBlackListTotalWaterpolo,
            $imageBlackListWasserballecke,
            $imageBlackListSsvEsslingen,
            $imageBlackListDeutscheWasserballLiga,
            $imageBlackListProRecco,
            $imageBlackListDanceHr
        );

        $content = file_get_contents($url);
        $crawler = new Crawler($content);

        $this->removeWordpressContentRelations($url, $crawler);
        $images = $this->getFilterBySelector($crawler, 'img');

        foreach ($images as $image) {
            if (!method_exists($image, 'getAttribute')) {
                continue;
            }

            $src = $image->getAttribute('src');
            foreach ($imageBlackList as $needle) {
                if (false !== strpos(strtolower($src), strtolower($needle))) {
                    continue 2;
                }
            }

            return $src;
        }

        return false;
    }

    private function getFilterBySelector(Crawler $crawler, $selector): Crawler
    {
        return $crawler->filter($selector);
    }

    private function removeWordpressContentRelations($url, Crawler $crawler): void
    {
        $crawler->filter('.section-related-ul')->each(function (Crawler $crawler) {
            $node = $crawler->getNode(0);
            $node->parentNode->removeChild($node);
        });

        if (false !== strpos($url, 'ssv-esslingen.de')) {
            $crawler->filter('.page-img')->each(function (Crawler $crawler) {
                $node = $crawler->getNode(0);
                $node->parentNode->removeChild($node);
            });
        }

        if (false !== strpos($url, 'deutsche-wasserball-liga.de')) {
            $crawler->filter('#carousel-eyecatcher')->each(function (Crawler $crawler) {
                $node = $crawler->getNode(0);
                $node->parentNode->removeChild($node);
            });

            $crawler->filter('.content-header')->each(function (Crawler $crawler) {
                $node = $crawler->getNode(0);
                $node->parentNode->removeChild($node);
            });
        }

        if (false !== strpos($url, 'www.prorecco.it')) {
            $crawler->filter('.wpls-logo-showcase-slider-wrp')->each(function (Crawler $crawler) {
                $node = $crawler->getNode(0);
                $node->parentNode->removeChild($node);
            });

            $crawler->filter('.blog_slider_ul')->each(function (Crawler $crawler) {
                $node = $crawler->getNode(0);
                $node->parentNode->removeChild($node);
            });
        }

        if (false !== strpos($url, 'total-waterpolo.com')) {
            $crawler->filter('.hustle-ui')->each(function (Crawler $crawler) {
                $node = $crawler->getNode(0);
                $node->parentNode->removeChild($node);
            });
        }
    }

    public function getNewsItemsFromUrl($url): array
    {
        try {
            $content = file_get_contents($url.'/feed/');
        } catch (\Exception $exception) {
            throw new \Exception($exception);
        }

        $xml = simplexml_load_string($content);
        $json = json_encode($xml);
        $newsFeed = json_decode($json, true)['channel']['item'];

        $content = file_get_contents($url);
        $crawler = new Crawler($content);

        $this->removeWordpressContentRelations($url, $crawler);
        $images = $this->getFilterBySelector($crawler, 'img');
        $titles = $this->getFilterBySelector($crawler, 'h1');
        $links = $this->getFilterBySelector($crawler, '.btn-more');

        $news = [];
        foreach ($titles as $key => $title) {
            $title = $title->textContent;

            $feedKey = array_search($title, array_column($newsFeed, 'title'));

            $src = $images->eq($key)->attr('src');
            $link = $links->eq($key)->attr('href');
            $properties = [
                'title' => $title,
                'image' => $src,
                'link' => $link,
                'pubDate' => $newsFeed[$feedKey]['pubDate'],
            ];
            $news[] = $properties;
        }

        return $news;
    }
}
