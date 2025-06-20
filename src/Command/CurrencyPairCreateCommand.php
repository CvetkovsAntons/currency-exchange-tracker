<?php

namespace App\Command;

use App\Repository\CurrencyRepository;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:currency-pair:create',
    description: 'Creates a new currency pair',
)]
class CurrencyPairCreateCommand extends AbstractCommand
{
    private const string ARG_FROM_CURRENCY_CODE = 'from-currency-code';
    private const string ARG_TO_CURRENCY_CODE = 'to-currency-code';

    public function __construct(
        private readonly CurrencyRepository $repository,
        private readonly LoggerInterface    $logger,
    )
    {
        parent::__construct($logger);
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

    protected function process(InputInterface $input, OutputInterface $output, SymfonyStyle $io): void
    {

    }

}
