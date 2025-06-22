<?php

namespace App\Command;

use App\Entity\CurrencyPair;
use App\Enum\Argument;
use App\Exception\CurrencyPairException;
use App\Factory\CurrencyPairFactory;
use App\Repository\CurrencyPairRepository;
use App\Repository\CurrencyRepository;
use App\Service\Domain\CurrencyPairService;
use App\Service\Domain\CurrencyService;
use http\Exception\InvalidArgumentException;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:create:currency-pair',
    description: 'Creates a new currency pair',
)]
class CreateCurrencyPairCommand extends AbstractCommand
{
    public function __construct(
        private readonly CurrencyPairService $pairService,
        private readonly CurrencyService     $currencyService,
        private readonly LoggerInterface     $logger,
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
        $io->section('Currency pair creation');

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

        $question = sprintf(
            'Are you sure you want to create %s->%s pair?',
            $fromCurrencyCode,
            $toCurrencyCode,
        );

        if ($this->askYesNo($question, $io)->isNo()) {
            $io->warning('Currency pair creation process has been canceled!');
            return;
        }

        $io->info('Currency pair creation is in the progress. It can take up some time...');

        $this->pairService->createFromCodes($fromCurrencyCode, $toCurrencyCode);

        $io->success(sprintf(
            "%s-%s currency pair has been created!",
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
