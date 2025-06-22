<?php

namespace App\Command;

use App\Entity\Currency;
use App\Enum\Argument;
use App\Service\Console\CommandInvokerService;
use App\Service\Domain\CurrencyPairService;
use App\Service\Domain\CurrencyService;
use App\Service\Domain\ExchangeRateService;
use InvalidArgumentException;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:update:exchange-rate',
    description: 'Create new or updates existing currency pair exchange rate',
)]
class UpdateExchangeRateCommand extends AbstractCommand
{
    public function __construct(
        private readonly CurrencyService     $currencyService,
        private readonly ExchangeRateService $rateService,
        private readonly LoggerInterface     $logger, private readonly CurrencyPairService $pairService, private readonly CommandInvokerService $commandService,
    )
    {
        parent::__construct($logger);
    }

    protected function configure(): void
    {
        $this
            ->addArgument(
                name: Argument::CURRENCY_FROM->value,
                mode: InputArgument::OPTIONAL,
                description: 'Currency code (e.g. USD)'
            )
            ->addArgument(
                name: Argument::CURRENCY_TO->value,
                mode: InputArgument::OPTIONAL,
                description: 'Currency name (e.g. USD)'
            );
    }

    protected function process(InputInterface $input, OutputInterface $output, SymfonyStyle $io): void
    {
        $io->title('Create/update exchange rate');

        $fromCurrency= $this->getCurrency(
            argument: Argument::CURRENCY_FROM,
            input: $input,
            output: $output,
            io: $io
        );

        $toCurrency = $this->getCurrency(
            argument: Argument::CURRENCY_TO,
            input: $input,
            output: $output,
            io: $io
        );

        $io->info(sprintf(
            '%s-%s exchange rate update is in the progress. It can take up some time...',
            $fromCurrency->getCode(),
            $toCurrency->getCode()
        ));

        $currencyPair = $this->pairService->get($fromCurrency, $toCurrency);
        if (is_null($currencyPair)) {
            $io->warning(sprintf(
                "Currency pair %s-%s doesn't exists. It will be created",
                $fromCurrency->getCode(),
                $toCurrency->getCode()
            ));

            $currencyPairCreate = new ArrayInput([
                'command' => 'app:create:currency-pair',
                Argument::CURRENCY_FROM->value => $fromCurrency->getCode(),
                Argument::CURRENCY_TO->value=> $toCurrency->getCode(),
            ]);

            $currencyPairCreate->setInteractive(false);

            $this->getApplication()->doRun($currencyPairCreate, $output);

            $currencyPair = $this->pairService->get($fromCurrency, $toCurrency);
        }

        $io->info('Exchange rate update process is in the progress. It can take up some time...');

        $exchangeRate = $this->rateService->get($currencyPair);

        if (is_null($exchangeRate)) {
            $this->rateService->create($currencyPair);
        } else {
            $this->rateService->update($exchangeRate);
        }

        $io->success(sprintf(
            "%s-%s exchange rate has been updated!",
            $fromCurrency->getCode(),
            $toCurrency->getCode()
        ));
    }

    private function getCurrency(
        Argument        $argument,
        InputInterface  $input,
        OutputInterface $output,
        SymfonyStyle    $io
    ): Currency
    {
        $currencyCode = $input->getArgument($argument->value);

        if (is_null($currencyCode)) {
            $question = match ($argument) {
                Argument::CURRENCY_FROM => 'Currency that will be exchanged (from) (e.g. PHP)',
                Argument::CURRENCY_TO => 'Currency that will be exchanged to (to) (e.g. PHP)',
                default => throw new InvalidArgumentException(sprintf('Wrong argument: %s', $argument->value))
            };

            $currencyCodes = $this->currencyService->getAllCodes();

            $question = new ChoiceQuestion($question, $currencyCodes)
                ->setNormalizer(fn($v) => is_numeric($v) ? $currencyCodes[$v] : $v);

            $currencyCode = $io->askQuestion($question);
        }

        $currency = $this->currencyService->get($currencyCode);
        if (is_null($currency)) {
            $io->warning(sprintf("Currency %s doesn't exists. It will be created", $currencyCode));

            $currencyCreate = new ArrayInput([
                'command' => 'app:create:currency',
                Argument::CURRENCY->value => $currencyCode,
            ]);

            $currencyCreate->setInteractive(false);

            $this->getApplication()->doRun($currencyCreate, $output);

            $currency =  $this->currencyService->get($currencyCode);
        }

        return $currency;
    }

}
