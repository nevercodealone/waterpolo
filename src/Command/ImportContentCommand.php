<?php


namespace App\Command;


use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ImportContentCommand extends Command
{
    protected static $defaultName = 'app:import:content';

    protected function configure()
    {
        $this
            ->setDescription('Import from APIs')
            ->setHelp('This command will get all new content, clear the cache and fill it');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln([
            'Import process',
            '============',
            '',
        ]);

    }
}
