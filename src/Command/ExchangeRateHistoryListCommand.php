<?php

namespace App\Command;

use App\Entity\ExchangeRateHistory;
use App\Service\Domain\ExchangeRateHistoryService;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:exchange-rate-history:list',
    description: 'List of exchange rate history',
)]
class ExchangeRateHistoryListCommand extends AbstractCommand
{
    public function __construct(
        private readonly ExchangeRateHistoryService $service,
        private readonly LoggerInterface            $logger,
    )
    {
        parent::__construct($this->logger);
    }

    protected function process(InputInterface $input, OutputInterface $output, SymfonyStyle $io): void
    {
        $io->title('Exchange Rate History List');

        $exchangeRates = $this->service->getAll();

        if (empty($exchangeRates)) {
            $io->warning('No exchange rates found.');
            return;
        }

        $rows = array_map(
            callback: function (ExchangeRateHistory $row) {
                $pair = $row->getCurrencyPair();
                $from = $pair->getFromCurrency();
                $to = $pair->getToCurrency();

                return [
                    sprintf('%s -> %s', $from->getCode(), $to->getCode()),
                    $row->getRate(),
                    $row->getCreatedAt()->format('Y-m-d H:i:s'),
                ];
            },
            array: $exchangeRates
        );

        $io->table(['Currency pair', 'Rate', 'Synced at'], $rows);
    }

}
