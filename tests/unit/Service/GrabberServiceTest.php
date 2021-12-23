<?php

namespace App\Tests\unit\Service;

use App\Grabber\WebsiteGrabber;
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
}
