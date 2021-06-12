<?php

declare(strict_types=1);

namespace Tests\Unit\Command;

use App\Command\ImportContentCommand;
use App\Service\GrabberService;
use App\Service\NewsService;
use App\Service\YouTubeService;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

final class ImportContentCommandTest extends KernelTestCase
{
    public function testExecuteWillOutputEndJobWhenRunSuccessfull()
    {
        $kernel = self::bootKernel();

        $application = new Application($kernel);

        $youTubeService = $this->createMock(YouTubeService::class);
        $youTubeService->expects($this->once())
            ->method('getVideoByKeywordsFromApi')
            ->willReturn([
                'videos' => [],
            ]);

        $newsService = $this->createMock(NewsService::class);
        $itemMock = $this->createMock(CacheItemInterface::class);
        $itemMock->expects($this->once())
            ->method('set')
            ->willReturn(true);
        $cacheItemPoolInterface = $this->createMock(CacheItemPoolInterface::class);
        $cacheItemPoolInterface->expects($this->once())
            ->method('getItem')
            ->willReturn($itemMock);
        $grabberService = $this->createMock(GrabberService::class);
        $grabberService->expects($this->once())
            ->method('getItems')
            ->willReturn([
                'news' => [],
                'sourceDomains' => [],
                ]);
        $application->add(new ImportContentCommand(
            $youTubeService,
            $newsService,
            $cacheItemPoolInterface,
            $grabberService
        ));

        $command = $application->find('app:import:content');
        $commandTester = new CommandTester($command);
        $commandTester->execute([
        ]);

        // the output of the command in the console
        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('End job.', $output);
    }
}
