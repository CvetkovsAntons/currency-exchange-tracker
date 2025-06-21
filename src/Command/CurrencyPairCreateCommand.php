<?php

namespace App\Command;

use App\Exception\CurrencyPairException;
use App\Factory\CurrencyPairFactory;
use App\Repository\CurrencyPairRepository;
use App\Repository\CurrencyRepository;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:currency-pair:create',
    description: 'Creates a new currency pair',
)]
class CurrencyPairCreateCommand extends AbstractCommand
{
    public function __construct(
        private readonly CurrencyRepository $currencyRepository,
        private readonly CurrencyPairRepository $pairRepository,
        private readonly CurrencyPairFactory $factory,
        private readonly LoggerInterface    $logger,
    )
    {
        parent::__construct($logger);
    }

    protected function process(InputInterface $input, OutputInterface $output, SymfonyStyle $io): void
    {
        $io->section('Currency pair creation');

        [$fromCurrency, $toCurrency] = $this->getCurrencies($io);

        if ($this->pairRepository->exists($fromCurrency, $toCurrency)) {
            throw new CurrencyPairException(sprintf(
                'Currency pair %s->%s already exists',
                $fromCurrency->getCode(),
                $toCurrency->getCode()
            ));
        }

        $question = sprintf(
            'Are you sure you want to create %s->%s pair?',
            $fromCurrency->getCode(),
            $toCurrency->getCode(),
        );

        if ($this->askYesNo($question, $io)->isNo()) {
            $io->warning('Currency pair creation process has been canceled!');
            return;
        }

        $io->info('Currency pair creation is in the progress. It can take up some time...');

        $currencyPair = $this->factory->create($fromCurrency, $toCurrency);

        $this->pairRepository->save($currencyPair);

        $io->success(sprintf(
            "%s-%s currency pair has been created!",
            $fromCurrency->getCode(),
            $toCurrency->getCode()
        ));
    }

    private function getCurrencies(SymfonyStyle $io): array
    {
        $currencyCodes = $this->currencyRepository->getAllCodes();

        $question = function (string $question) use ($currencyCodes) {
            return new ChoiceQuestion($question, $currencyCodes)
                ->setNormalizer(fn($v) => is_numeric($v) ? $currencyCodes[$v] : $v);
        };

        $fromQuestion = $question('Currency that will be exchanged (from) (e.g. PHP)');
        $from = $this->currencyRepository->getByCode($io->askQuestion($fromQuestion));

        $toQuestion = $question('Currency that will be exchanged to (to) (e.g. PHP)');
        $to = $this->currencyRepository->getByCode($io->askQuestion($toQuestion));

        return [$from, $to];
    }

}
