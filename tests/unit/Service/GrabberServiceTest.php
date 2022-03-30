<?php

namespace App\Tests\unit\Service;

use App\Grabber\WebsiteGrabber;
use App\Grabber\WebsiteGrabberInterface;
use App\Grabber\WordpressGrabber;
use App\Handler\ImageHandler;
use App\Service\GrabberService;
use PHPUnit\Framework\TestCase;

class GrabberServiceTest extends TestCase
{
    public function testGetNewsByFirstDomainWhenPageTypeIsWebsite()
    {
        $wordpressGrabber = $this->createMock(WordpressGrabber::class);
        $websiteGrabber = new class implements WebsiteGrabberInterface{
            public function getNewsItemsFromUrl(string $url, array $properties): array|false
            {
                return [
                    'unit' => 'test'
                ];
            }
        };
        $imageHandler = $this->createMock(ImageHandler::class);

        $sourceDomains = [
            [
                'domain' => "www.deutsche-wasserball-liga.de",
                'page-type' => "website",
                'image' => 'img',
                'title' => 'h1',
                'more-link' => '.btn-more',
            ]
        ];
        $grabberService = new GrabberService($wordpressGrabber, $websiteGrabber, $imageHandler, $sourceDomains);
        $items = $grabberService->getItems();

        self::assertCount(1, $items['news']);
        self::assertSame('test', $items['news'][0]);
    }
}
