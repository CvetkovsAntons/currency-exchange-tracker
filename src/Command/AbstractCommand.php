<?php

namespace App\Command;

use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Throwable;

abstract class AbstractCommand extends Command
{
    public function __construct(private readonly LoggerInterface $logger)
    {
        parent::__construct();
    }

    protected final function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        try {
            $this->process($input, $output, $io);
            return Command::SUCCESS;
        } catch (Throwable $e) {
            $this->processError($e, $io);
            return Command::FAILURE;
        }
    }

    abstract protected function process(InputInterface $input, OutputInterface $output, SymfonyStyle $io): void;

    protected function processError(Throwable $e, SymfonyStyle $io): void
    {
        $io->error(sprintf("Error occurred during command execution: %s", $e->getMessage()));
        $this->logger->error($e);
    }

}
