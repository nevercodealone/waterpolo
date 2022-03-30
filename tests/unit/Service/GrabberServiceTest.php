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
    public function testGetItemsReduceSourceDomainsOnDebugFirstDomain()
    {
        $wordpressGrabber = $this->createMock(WordpressGrabber::class);
        $websiteGrabber = $this->createMock(WebsiteGrabber::class);
        $imageHandler = $this->createMock(ImageHandler::class);

        $grabberService = new GrabberService($wordpressGrabber, $websiteGrabber, $imageHandler);
        $sourceDomains = $grabberService->getItems('firstDomain')['sourceDomains'];

        $this->assertCount(1, $sourceDomains);
    }

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

        $grabberService = new GrabberService($wordpressGrabber, $websiteGrabber, $imageHandler);
        $items = $grabberService->getItems('firstDomain');

        self::assertCount(1, $items['news']);
        self::assertSame('test', $items['news'][0]);
    }
}
