<?php

namespace App\Command;

use App\Factory\CurrencyFactory;
use App\Provider\CurrencyProvider;
use App\Repository\CurrencyRepository;
use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Throwable;

#[AsCommand(
    name: 'app:currency:create',
    description: 'Creates a new currency',
)]
class CurrencyCreateCommand extends AbstractCommand
{
    private const string ARG_CURRENCY_CODE = 'currency-code';

    public function __construct(
        private readonly CurrencyRepository $repository,
        private readonly CurrencyFactory    $factory,
        private readonly CurrencyProvider   $provider,
        private readonly LoggerInterface    $logger,
    )
    {
        parent::__construct($logger);
    }

    protected function configure(): void
    {
        $this->addArgument(
            name: self::ARG_CURRENCY_CODE,
            mode: InputArgument::REQUIRED,
            description: 'Unique currency code. Example: EUR'
        );
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
        $io->info('Currency creation is in the progress. It can take up some time...');

        $currencyCode = $input->getArgument(self::ARG_CURRENCY_CODE);
        $currencyCode = strtoupper($currencyCode);

        if (!preg_match('/^[A-Z]{3}$/', $currencyCode)) {
            throw new Exception('Incorrect currency code format as per ISO 4217 standard. Example: EUR');
        }

        $currency = $this->provider->getCurrency($currencyCode);

        if (empty($currency)) {
            throw new Exception(sprintf("Data for %s not found", $currencyCode));
        }

        $currency = $this->factory->create($currency);

        $this->repository->save($currency);

        $io->success(sprintf("%s (%s) has been created!", $currency->getName(), $currencyCode));
    }

}
