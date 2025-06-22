<?php

namespace App\Command;

use App\Exception\CurrencyApiException;
use App\Exception\CurrencyCodeException;
use App\Service\Domain\CurrencyService;
use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
#[AsCommand(
    name: 'app:create:currency',
    description: 'Creates a new currency',
)]
class CreateCurrencyCommand extends AbstractCommand
{
    public function __construct(
        private readonly CurrencyService $service,
        private readonly LoggerInterface $logger,
    )
    {
        parent::__construct($logger);
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @param SymfonyStyle $io
     * @return void
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws CurrencyApiException
     * @throws Exception
     */
    public function process(InputInterface $input, OutputInterface $output, SymfonyStyle $io): void
    {
        $io->section('Currency creation');

        $validation = function (string $input) {
            if ($this->service->exists($input)) {
                throw new CurrencyCodeException($input, sprintf('Currency %s already exists.', $input));
            }
        };

        $currencyCode = $this->inputCurrencyCode(
            question: 'Currency code to import from API to database (e.g. EUR)',
            io: $io,
            validation: $validation,
        );

        $question = sprintf('Are you sure you want to import %s?', $currencyCode);

        if ($this->askYesNo($question, $io)->isNo()) {
            $io->warning('Currency creation process has been canceled!');
            return;
        }

        $io->info('Currency creation is in the progress. It can take up some time...');

        $currency = $this->service->create($currencyCode);

        $io->success(sprintf(
            '%s (%s) has been created!',
            $currency->getName(),
            $currencyCode
        ));
    }

}
