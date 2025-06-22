<?php

namespace App\Command;

use App\Enum\Argument;
use App\Service\Domain\CurrencyPairService;
use App\Service\Domain\CurrencyService;
use App\Service\Domain\ExchangeRateService;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
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
        private readonly LoggerInterface     $logger, private readonly CurrencyPairService $currencyPairService,
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

        $fromCurrencyCode = $this->getCurrencyCode(
            argument: Argument::CURRENCY_FROM,
            input: $input,
            io: $io
        );

        $toCurrencyCode = $this->getCurrencyCode(
            argument: Argument::CURRENCY_TO,
            input: $input,
            io: $io
        );

        $io->info(sprintf(
            '%s-%s exchange rate update is in the progress. It can take up some time...',
            $fromCurrencyCode,
            $toCurrencyCode
        ));

        $currencyPair = $this->currencyPairService->getByCodes($fromCurrencyCode, $toCurrencyCode);

        $this->rateService->updateByPair($currencyPair);

        $io->success(sprintf(
            "%s-%s exchange rate has been updated!",
            $fromCurrencyCode,
            $toCurrencyCode
        ));
    }

    private function getCurrencyCode(Argument $argument, InputInterface $input, SymfonyStyle $io): string
    {
        $currencyCode = $input->getArgument($argument->value);

        if (is_null($currencyCode)) {
            $question = match ($argument) {
                Argument::CURRENCY_FROM => 'Currency that will be exchanged (from) (e.g. PHP)',
                Argument::CURRENCY_TO => 'Currency that will be exchanged to (to) (e.g. PHP)',
            };

            $currencyCodes = $this->currencyService->getAllCodes();

            $question = new ChoiceQuestion($question, $currencyCodes)
                ->setNormalizer(fn($v) => is_numeric($v) ? $currencyCodes[$v] : $v);

            $currencyCode = $io->askQuestion($question);
        }

        return $currencyCode;
    }

}
