<?php

namespace App\Command;

use App\Entity\ExchangeRate;
use App\Service\Domain\ExchangeRateService;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:exchange-rate:list',
    description: 'List of exchange rates',
)]
class ExchangeRateListCommand extends AbstractCommand
{
    public function __construct(
        private readonly ExchangeRateService $service,
        private readonly LoggerInterface     $logger,
    )
    {
        parent::__construct($this->logger);
    }

    protected function process(InputInterface $input, OutputInterface $output, SymfonyStyle $io): void
    {
        $io->title('Exchange Rate List');

        $exchangeRates = $this->service->getAll();

        if (empty($exchangeRates)) {
            $io->warning('No exchange rates found.');
            return;
        }

        $rows = array_map(
            callback: function (ExchangeRate $row) {
                $pair = $row->getCurrencyPair();
                $from = $pair->getFromCurrency();
                $to = $pair->getToCurrency();

                return [
                    sprintf('%s -> %s', $from->getCode(), $to->getCode()),
                    $row->getRate(),
                    $row->getUpdatedAt()->format('Y-m-d H:i:s'),
                    $pair->getIsTracked() ? 'true' : 'false',
                ];
            },
            array: $exchangeRates
        );

        $io->table(['Currency pair', 'Rate', 'Synced at', 'Is tracked'], $rows);
    }

}
