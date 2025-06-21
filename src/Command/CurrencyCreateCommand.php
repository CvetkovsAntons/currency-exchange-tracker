<?php

namespace App\Command;

use App\Exception\CurrencyCodeException;
use App\Factory\CurrencyFactory;
use App\Provider\CurrencyProvider;
use App\Repository\CurrencyRepository;
use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
#[AsCommand(
    name: 'app:currency:create',
    description: 'Creates a new currency',
)]
class CurrencyCreateCommand extends AbstractCommand
{
    public function __construct(
        private readonly CurrencyRepository $repository,
        private readonly CurrencyFactory    $factory,
        private readonly CurrencyProvider   $provider,
        private readonly LoggerInterface    $logger,
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
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     * @throws Exception
     */
    public function process(InputInterface $input, OutputInterface $output, SymfonyStyle $io): void
    {
        $io->section('Currency creation');

        $validation = function (string $input) {
            if ($this->repository->exists($input)) {
                throw new CurrencyCodeException(sprintf('Currency %s already exists.', $input));
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

        $currency = $this->provider->getCurrency($currencyCode);

        if (empty($currency)) {
            throw new Exception(sprintf("Data for %s not found", $currencyCode));
        }

        $currency = $this->factory->create($currency);

        $this->repository->save($currency);

        $io->success(sprintf(
            '%s (%s) has been created!',
            $currency->getName(),
            $currencyCode
        ));
    }

}
