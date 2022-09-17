<?php

namespace App\Service;

use App\Grabber\WebsiteGrabberInterface;
use App\Grabber\WordpressGrabber;
use App\Handler\ImageHandler;
use function array_merge;
use Exception;
use Symfony\Component\DomCrawler\Crawler;

class GrabberService
{
    /**
     * @param array<array{
     *     domain: string,
     *     page-type: "website"|"wordpress",
     *     image?: string,
     *     title?: string,
     *     more-link?: string,
     *     remove-links-selector?: string[]
     *     tags?: string[]
     * }> $sourceDomains
     * @param string[] $removeSelectors
     */
    public function __construct(
        private WordpressGrabber $wordpressGrabber,
        private WebsiteGrabberInterface $websiteGrabber,
        private ImageHandler $imageHandler,
        private array $sourceDomains,
        private array $removeSelectors,
    ) {
    }

    /**
     * @return array<string, array>
     *
     * @throws Exception
     */
    public function getItems(): array
    {
        $allNews = [];
        foreach ($this->sourceDomains as $sourceDomain) {
            if ('website' === $sourceDomain['page-type']) {
                $news = $this->websiteGrabber->getNewsItemsFromUrl('https://'.$sourceDomain['domain'], $sourceDomain);
                if ($news) {
                    $allNews = [...$allNews, ...$news];
                }
            } else {
                $feedUrl = 'https://'.$sourceDomain['domain'].'/feed/';
                try {
                    $news = $this->wordpressGrabber->getItemsFromFeedUrl($feedUrl);

                    foreach ($news as $key => $item) {
                        if (in_array('tags', $sourceDomain, true)) {
                            if (!isset($item[$sourceDomain['tags']]) || !is_array($item[$sourceDomain['tags']])) {
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

                        $src = $this->getImageFromUrl($link, $sourceDomain['remove-links-selector'] ?? []);

                        if (!$src) {
                            unset($news[$key]);
                            continue;
                        }

                        $filename = $this->imageHandler->saveFileFromUrl($src);

                        if ('' === $filename) {
                            unset($news[$key]);
                            continue;
                        }

                        $news[$key]['image'] = $filename;
                        $news[$key]['link'] = $link;
                        $news[$key]['url'] = $sourceDomain['domain'];
                    }

                    $allNews = [...$allNews, ...$news];
                } catch (Exception $exception) {
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

    private function getImageFromUrl(string $url, array $removeLinkSelector): string|false
    {
        $imageBlackList = require __DIR__.'/../../config/disallowlist.php';

        $content = file_get_contents($url);
        if (!$content) {
            return false;
        }
        $crawler = new Crawler($content);

        $this->removeWordpressContentRelations($removeLinkSelector, $crawler);
        $images = $this->getFilterBySelector($crawler, 'img');

        foreach ($images as $image) {
            if (!method_exists($image, 'getAttribute')) {
                continue;
            }

            $src = $image->getAttribute('src');
            foreach ($imageBlackList as $singleImageBlackList) {
                if (str_contains(strtolower($src), strtolower($singleImageBlackList))) {
                    continue 2;
                }
            }

            return $src;
        }

        return false;
    }

    private function getFilterBySelector(Crawler $crawler, string $selector): Crawler
    {
        return $crawler->filter($selector);
    }

    private function removeWordpressContentRelations(array $removeLinkSelector, Crawler $crawler): void
    {
        $removeLinkSelector = array_merge($removeLinkSelector, $this->removeSelectors);
        foreach ($removeLinkSelector as $singleRemoveLinkSelector) {
            $crawler->filter($singleRemoveLinkSelector)->each(function (Crawler $crawler) {
                $domNode = $crawler->getNode(0);
                if (null === $domNode) {
                    return;
                }
                $domNode->parentNode?->removeChild($domNode);
            });
        }
    }
}
