<?php

namespace App\Command;

use App\Entity\Currency;
use App\Enum\Argument;
use App\Enum\YesNo;
use App\Service\Domain\CurrencyService;
use InvalidArgumentException;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\MissingInputException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;
use Throwable;

abstract class AbstractCommand extends Command
{
    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly CurrencyService $currencyService,
    )
    {
        parent::__construct();
    }

    protected final function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        try {
            $this->process($input, $output, $io);
            return Command::SUCCESS;
        } catch (Throwable $e) {
            $this->processError($e, $io);
            return Command::FAILURE;
        }
    }

    abstract protected function process(
        InputInterface $input,
        OutputInterface $output,
        SymfonyStyle $io
    ): void;

    protected function processError(Throwable $e, SymfonyStyle $io): void
    {
        $io->error(sprintf("Error occurred during command execution: %s", $e->getMessage()));
        $this->logger->error($e);
    }

    protected function getCurrency(
        Argument $argument,
        InputInterface $input,
        SymfonyStyle $io,
        ?string $question = null,
    ): Currency
    {
        $currencyCode = $input->getArgument($argument->value);
        if (is_null($currencyCode)) {
            $question ??= 'Select a currency';

            $currencyCodes = $this->currencyService->getAllCodes();
            $question = new ChoiceQuestion($question, $currencyCodes)
                ->setNormalizer(fn($v) => is_numeric($v) ? $currencyCodes[$v] : $v);

            $currencyCode = $io->askQuestion($question);
        }

        $currency = $this->currencyService->get($currencyCode);
        if (is_null($currency)) {
            $io->warning(sprintf(
                "Currency %s doesn't exist. Will be created",
                $currencyCode
            ));

            $currency = $this->currencyService->create($currencyCode);

            $io->success(sprintf('Currency %s has been created', $currencyCode));
        }

        return $currency;
    }

}
