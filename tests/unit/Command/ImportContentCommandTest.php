<?php

declare(strict_types=1);

namespace Tests\Unit\Command;


use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;
use App\Command\ImportContentCommand;
use App\Service\GrabberService;
use App\Service\NewsService;
use App\Service\YouTubeService;
use PHPUnit\Framework\TestCase;
use Psr\Cache\CacheItemPoolInterface;

final class ImportContentCommandTest extends KernelTestCase
{
    public function testExecute()
    {
        $kernel = static::createKernel();
        $application = new Application($kernel);

        $command = $application->find('app:import:content');
        $commandTester = new CommandTester($command);
        $commandTester->execute([
            
        ]);

        // the output of the command in the console
        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('End job.', $output);

        // ...
    }
}
