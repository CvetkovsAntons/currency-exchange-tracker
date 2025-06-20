<?php

namespace App\Command;

use App\Repository\CurrencyRepository;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Throwable;

#[AsCommand(
    name: 'app:currency-pair:create',
    description: 'Creates a new currency pair',
)]
class CurrencyPairCreateCommand extends Command
{
    private const string ARG_FROM_CURRENCY_CODE = 'from-currency-code';
    private const string ARG_TO_CURRENCY_CODE = 'to-currency-code';

    public function __construct(
        private readonly CurrencyRepository $repository,
        private readonly LoggerInterface    $logger,
    )
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument(
                name: self::ARG_FROM_CURRENCY_CODE,
                mode: InputArgument::REQUIRED,
                description: 'Unique currency code that will be exchanged. Example: EUR'
            )
            ->addArgument(
                name: self::ARG_TO_CURRENCY_CODE,
                mode: InputArgument::REQUIRED,
                description: 'Unique currency code that will be exchanged to. Example: EUR'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        try {
            $from_currency_code = $input->getArgument(self::ARG_FROM_CURRENCY_CODE);
            $to_currency_code = $input->getArgument(self::ARG_TO_CURRENCY_CODE);

            $io->note(sprintf('You passed an argument 1: %s', $from_currency_code));
            $io->note(sprintf('You passed an argument: %s', $to_currency_code));

            if ($input->getOption('option1')) {
                // ...
            }

            $io->success('You have a new command! Now make it your own! Pass --help to see your options.');

            return Command::SUCCESS;
        } catch (Throwable $e) {
            $io->error(sprintf("Couldn't create currency: %s", $e->getMessage()));
            $this->logger->error($e);
            return Command::FAILURE;
        }
    }
}
