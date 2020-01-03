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
        'wasserballecke.de',
        'total-waterpolo.com',
        'spandau04.de',
        'waspo98.de'
    ];

    public function __construct(KernelInterface $appKernel, Filesystem $fileSystem)
    {
        $this->appKernel = $appKernel;
        $this->fileSystem = $fileSystem;

        $this->tmpFolder = $this->appKernel->getProjectDir() . '/public/tmp/photos/';

        if (!$this->fileSystem->exists($this->tmpFolder)) {
            $this->fileSystem->mkdir($this->tmpFolder, 0777);
        }
    }

    public function getItems(): array
    {

        $allNews = [];

        foreach ($this->sourceDomains as $sourceDomain) {
            $content = file_get_contents('https://www.' . $sourceDomain . '/feed/');

            if ('spandau04' === $sourceDomain) {
                $content = str_replace(['<![CDATA[', ']]>', '<p>&nbsp;</p>'], '', $content);
            }

            $xml = simplexml_load_string($content);
            $json = json_encode($xml);
            $news = json_decode($json, true)['channel']['item'];

            $news = array_slice($news, 0, 6);

            foreach ($news as $key => $item) {
                if ('spandau04' === $sourceDomain) {
                    if (!is_array($item['category']) || !in_array('Wasserball', $item['category'])) {
                        unset($news[$key]);
                        continue;
                    }
                }

                $image = $this->getImageFromUrl($item);

                if (!$image) {
                    unset($news[$key]);
                    continue;
                }

                $filename = basename($image);
                $this->fileSystem->copy($image, $this->tmpFolder . $filename);

                $news[$key]['image'] = $filename;
                $news[$key]['url'] = $sourceDomain;
            }

            $allNews = array_merge($allNews, $news);
        }

        usort($allNews, function ($a, $b) {
            $actual = strtotime($a['pubDate']);
            $next = strtotime($b['pubDate']);
            return $actual - $next;
        });

        $array_reverse = array_reverse($allNews);

        return [
            'news' => $array_reverse,
            'sourceDomains' => $this->sourceDomains
        ];
    }

    private function getImageFromUrl($item)
    {
        $imageBlackListWaspo = [
            'logo-neu.jpg',
            'youtube.png',
            'instagram.png',
            'facebook.png'
        ];

        $imageBlackListSpandau = [
            'logo-spandau',
            '80x80',
            'plugins'
        ];

        $imageBlackListTotalWaterpolo = [
            'facebook.com',
            'w3.org',
            'water-polo-community.png',
            'Screen-Shot'
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
            'data:image'
        ];

        $imageBlackList = array_merge(
            $imageBlackListWaspo,
            $imageBlackListSpandau,
            $imageBlackListTotalWaterpolo,
            $imageBlackListWasserballecke
        );

        $content = file_get_contents($item['guid']);
        $crawler = new Crawler($content);
        $crawler->filter('.section-related-ul')->each(function (Crawler $crawler) {
            $node = $crawler->getNode(0);
            $node->parentNode->removeChild($node);
        });
        $images = $crawler->filter('img');

        foreach ($images as $image) {
            $src = $image->getAttribute('src');
            foreach ($imageBlackList as $needle) {
                if (strpos(strtolower($src), strtolower($needle)) !== false) {
                    continue 2;
                }
            }
            return $src;
        }

        return false;
    }
}
