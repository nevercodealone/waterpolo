<?php

namespace App\Service;

use App\Grabber\WebsiteGrabber;
use App\Grabber\WordpressGrabber;
use App\Handler\ImageHandler;
use Symfony\Component\DomCrawler\Crawler;

class GrabberService
{
    private string $tmpFolder;

    /** @var array<array> */
    private array $sourceDomains = [
        [
            'domain' => 'ssv-esslingen.de',
            'page-type' => 'wordpress',
            'tags' => ['category']
        ],
        [
            'domain' => 'h2o-polo.de',
            'page-type' => 'wordpress'
        ],
        [
            'domain' => 'www.deutsche-wasserball-liga.de',
            'page-type' => 'website',
            'image' => 'img',
            'title' => 'h1',
            'more-link' => '.btn-more'
        ],
        [
            'domain' => 'homepage.svl08.com',
            'page-type' => 'website',
            'image' => '#newscontainer > div > p:nth-child(3) > img',
            'title' => '.news_title',
            'more-link' => '#newscontainer > div > p:nth-child(3) > a'
        ],
        [
            'domain' => 'spandau04.de',
            'page-type' => 'wordpress',
            'tags' => ['category']
        ],
        [
            'domain' => 'wasserballecke.de',
            'page-type' => 'wordpress'
        ],
        [
            'domain' => 'total-waterpolo.com',
            'page-type' => 'wordpress'
        ],
        [
            'domain' => 'waspo98.de',
            'page-type' => 'wordpress'
        ],
        [
            'domain' => 'www.dance.hr',
            'page-type' => 'wordpress'
        ]
    ];

    public function __construct(
        private WordpressGrabber $wordpressGrabber,
        private WebsiteGrabber $websiteGrabber,
        private ImageHandler $imageHandler
    )
    {}

    /**
     * @return array<string, array>
     * @throws \Exception
     */
    public function getItems(?string $debug): array
    {
        if ('firstDomain' === $debug) {
            $this->sourceDomains = array_slice($this->sourceDomains, 0, 1);
        }

        $allNews = [];
        foreach ($this->sourceDomains as $properties) {
            if ($properties['page-type'] === 'website') {
                $news = $this->websiteGrabber->getNewsItemsFromUrl('https://'.$properties['domain'], $properties);
                $allNews = [...$allNews, ...$news];
            } else {
                $feedUrl = 'https://'.$properties['domain'].'/feed/';
                try {
                    $news = $this->wordpressGrabber->getItemsFromFeedUrl($feedUrl);

                    foreach ($news as $key => $item) {
                        if (in_array('tags', $properties, true)) {
                            if (!isset($item[$properties['tags']]) || !is_array($item[$properties['tags']])) {
                                unset($news[$key]);
                                continue;
                            }

                            $category = array_map('strtolower', $item['category']);
                            if (!in_array('wasserball', $category, true)) {
                                unset($news[$key]);
                                continue;
                            }
                        }
                        $link = $item['guid'];

                        $src = $this->getImageFromUrl($link);

                        if ($src === null) {
                            unset($news[$key]);
                            continue;
                        }

                        $filename = $this->imageHandler->saveFileFromUrl($src);

                        if ($filename === '') {
                            unset($news[$key]);
                            continue;
                        }

                        $news[$key]['image'] = $filename;
                        $news[$key]['link'] = $link;
                        $news[$key]['url'] = $properties['domain'];
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

    private function getImageFromUrl(string $url): ?string
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
                if (str_contains(strtolower($src), strtolower($needle))) {
                    continue 2;
                }
            }

            return $src;
        }

        return null;
    }

    private function getFilterBySelector(Crawler $crawler, string $selector): Crawler
    {
        return $crawler->filter($selector);
    }

    private function removeWordpressContentRelations(string $url, Crawler $crawler): void
    {
        $crawler->filter('.section-related-ul')->each(function (Crawler $crawler) {
            $node = $crawler->getNode(0);
            $node->parentNode->removeChild($node);
        });

        if (str_contains($url, 'ssv-esslingen.de')) {
            $crawler->filter('.page-img')->each(function (Crawler $crawler) {
                $node = $crawler->getNode(0);
                $node->parentNode->removeChild($node);
            });
        }

        if (str_contains($url, 'www.prorecco.it')) {
            $crawler->filter('.wpls-logo-showcase-slider-wrp')->each(function (Crawler $crawler) {
                $node = $crawler->getNode(0);
                $node->parentNode->removeChild($node);
            });

            $crawler->filter('.blog_slider_ul')->each(function (Crawler $crawler) {
                $node = $crawler->getNode(0);
                $node->parentNode->removeChild($node);
            });
        }

        if (str_contains($url, 'total-waterpolo.com')) {
            $crawler->filter('.hustle-ui')->each(function (Crawler $crawler) {
                $node = $crawler->getNode(0);
                $node->parentNode->removeChild($node);
            });
        }
    }
}
