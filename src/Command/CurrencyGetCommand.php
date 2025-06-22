<?php

namespace App\Command;

use App\Enum\Argument;
use App\Service\Domain\CurrencyService;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:currency:get',
    description: 'List of all currencies',
)]
class CurrencyGetCommand extends AbstractCommand
{
    public function __construct(
        private readonly CurrencyService $currencyService,
        private readonly LoggerInterface $logger
    )
    {
        parent::__construct($this->logger, $this->currencyService);
    }

    protected function configure(): void
    {
        $this
            ->addArgument(
                name: Argument::CURRENCY->value,
                mode: InputArgument::OPTIONAL,
                description: 'Currency code (e.g. PHP)'
            );
    }

    protected function process(InputInterface $input, OutputInterface $output, SymfonyStyle $io): void
    {
        $io->title('Currency');

        $currency = $this->getCurrency(Argument::CURRENCY, $input, $io);

        $currency = [
            $currency->getCode(),
            $currency->getName(),
            $currency->getSymbol(),
        ];

        $io->table(['Code', 'Name', 'Symbol'], [$currency]);
    }

}
