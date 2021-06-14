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
    /**
     * @var Application
     */
    private $application;

    /**
     * @var YouTubeService|\PHPUnit\Framework\MockObject\MockObject
     */
    private $youTubeService;

    /**
     * @var NewsService|\PHPUnit\Framework\MockObject\MockObject
     */
    private $newsService;

    /**
     * @var GrabberService|\PHPUnit\Framework\MockObject\MockObject
     */
    private $grabberService;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|CacheItemPoolInterface
     */
    private $cacheItem;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();

        $this->application = new Application($kernel);

        $this->youTubeService = $this->createMock(YouTubeService::class);
        $this->newsService = $this->createMock(NewsService::class);
        $this->cacheItem = $this->createMock(CacheItemPoolInterface::class);
        $this->grabberService = $this->createMock(GrabberService::class);

        $this->application->add(new ImportContentCommand(
            $this->youTubeService,
            $this->newsService,
            $this->cacheItem,
            $this->grabberService
        ));
    }

    public function testExecuteWillOutputEndJobWhenRunSuccessfull()
    {
        $this->youTubeService->expects($this->once())
            ->method('getVideoByKeywordsFromApi')
            ->willReturn([
                'videos' => [],
            ]);

        $item = $this->createMock(CacheItemInterface::class);
        $item->expects($this->once())
            ->method('set')
            ->willReturn(true);

        $this->cacheItem->expects($this->once())
            ->method('getItem')
            ->willReturn($item);

        $this->grabberService->expects($this->once())
            ->method('getItems')
            ->willReturn([
                'news' => [],
                'sourceDomains' => [],
                ]);

        $output = $this->executeCommandAndReturnOutput();
        $this->assertStringContainsString('End job.', $output);
    }

    public function testVideoCountValueWorksInDisplay()
    {
        $this->youTubeService->expects($this->once())
            ->method('getVideoByKeywordsFromApi')
            ->willReturn([
                'videos' => [],
            ]);

        $item = $this->createMock(CacheItemInterface::class);
        $item->expects($this->once())
            ->method('set')
            ->willReturn(true);

        $this->cacheItem->expects($this->once())
            ->method('getItem')
            ->willReturn($item);

        $this->grabberService->expects($this->once())
            ->method('getItems')
            ->willReturn([
                'news' => ['1', '2', '3', '4', '5'],
                'sourceDomains' => [],
            ]);

        $output = $this->executeCommandAndReturnOutput();
        $this->assertStringContainsString('Count:5', $output);
    }

    private function executeCommandAndReturnOutput(): string
    {
        $command = $this->application->find('app:import:content');
        $commandTester = new CommandTester($command);
        $commandTester->execute([
        ]);

        // the output of the command in the console
        $output = $commandTester->getDisplay();

        return $output;
    }
}
