<?php

namespace App\Command;

use App\Command\AbstractCommand;
use App\Entity\ExchangeRate;
use App\Enum\Argument;
use App\Service\Domain\CurrencyService;
use App\Service\Domain\ExchangeRateService;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
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
        private readonly LoggerInterface $logger,
        private readonly CurrencyService $currencyService,
        private readonly ExchangeRateService $exchangeRateService,
    )
    {
        parent::__construct($logger, $currencyService);
    }

//    protected function configure(): void
//    {
//        $this
//            ->addArgument(
//                name: Argument::CURRENCY->value,
//                mode: InputArgument::OPTIONAL,
//                description: 'List of currencies',
//            );
//    }

    protected function process(InputInterface $input, OutputInterface $output, SymfonyStyle $io): void
    {
        $io->title('Exchange Rate List');

        $exchangeRates = $this->exchangeRateService->getAll();

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
                    sprintf('%s-%s', $from->getCode(), $to->getCode()),
                    $row->getRate(),
                    $row->getUpdatedAt()->format('Y-m-d H:i:s'),
                ];
            },
            array: $exchangeRates
        );

        $io->table(['Currency pair', 'Rate', 'Synced'], $rows);
    }
}
