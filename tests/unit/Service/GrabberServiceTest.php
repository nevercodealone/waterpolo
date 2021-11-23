<?php

namespace App\Tests\unit\Service;

use App\Kernel;
use App\Service\GrabberService;
use Symfony\Component\Filesystem\Filesystem;
use PHPUnit\Framework\TestCase;

class GrabberServiceTest extends TestCase
{
    public function testGetItemsReduceSourceDomainsOnDebugFirstDomain()
    {
        $appKernel = $this->createMock(Kernel::class);
        $fileSystem = $this->createMock(Filesystem::class);

        $grabberService = new GrabberService($appKernel, $fileSystem);
        $sourceDomains = $grabberService->getItems('firstDomain')['sourceDomains'];

        $this->assertCount(1, $sourceDomains);
    }
}
