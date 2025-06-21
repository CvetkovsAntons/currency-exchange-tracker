<?php

namespace App\Command;

use App\Enum\YesNo;
use App\Exception\CurrencyCodeException;
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
    public function __construct(private readonly LoggerInterface $logger)
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

    abstract protected function process(InputInterface $input, OutputInterface $output, SymfonyStyle $io): void;

    protected function processError(Throwable $e, SymfonyStyle $io): void
    {
        $io->error(sprintf("Error occurred during command execution: %s", $e->getMessage()));
        $this->logger->error($e);
    }

    protected function inputCurrencyCode(string $question, SymfonyStyle $io, ?callable $validation = null): string
    {
        $validator = function (?string $input) use ($validation): string {
            if (empty($input)) {
                throw new MissingInputException('Currency code is required');
            }
            if (!preg_match('/^[A-Z]{3}$/', $input)) {
                throw new CurrencyCodeException('Incorrect currency code format as per ISO 4217 standard (e.g. PHP)');
            }
            if (!is_null($validation)) {
                $validation($input);
            }
            return $input;
        };

        $question = new Question($question)
            ->setNormalizer(fn($v) => strtoupper(trim($v)))
            ->setValidator($validator);

        return $io->askQuestion($question);
    }

    protected final function askYesNo(string $question, SymfonyStyle $io): YesNo
    {
        $question = new ChoiceQuestion($question, YesNo::classifier(), YesNo::YES->value);

        return YesNo::from($io->askQuestion($question));
    }

}
