<?php

namespace App\Command;

use App\Service\Domain\CurrencyService;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:currency:list',
    description: 'List of all currencies',
)]
class CurrencyListCommand extends AbstractCommand
{
    public function __construct(
        private readonly CurrencyService $currencyService,
        private readonly LoggerInterface $logger
    )
    {
        parent::__construct($this->logger);
    }

    protected function process(InputInterface $input, OutputInterface $output, SymfonyStyle $io): void
    {
        $io->title('Currency List');

        $currencies = $this->currencyService->getAll();

        if (empty($currencies)) {
            $io->warning('No currencies found.');
            return;
        }

        $rows = array_map(
            callback: fn($v) => [$v->getCode(), $v->getName(), $v->getSymbol()],
            array: $currencies
        );

        $io->table(['Code', 'Name', 'Symbol'], $rows);
    }

}
