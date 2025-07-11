<?php

namespace App\Command;

use App\Entity\Currency;
use App\Entity\CurrencyPair;
use App\Enum\Argument;
use App\Exception\Currency\CurrencyNotFoundException;
use App\Exception\Currency\DuplicateCurrencyCodeException;
use App\Exception\CurrencyApi\CurrencyApiUnavailableException;
use App\Exception\CurrencyApi\CurrencyDataNotFoundException;
use App\Exception\CurrencyApi\ExchangeRateNotFoundException as CurrencyApiExchangeRateNotFoundException;
use App\Exception\CurrencyPair\CurrencyPairNotFoundException;
use App\Exception\CurrencyPair\DuplicateCurrencyPairException;
use App\Exception\ExchangeRate\DuplicateExchangeRateException;
use App\Exception\ExchangeRate\ExchangeRateNotFoundException;
use App\Exception\ExternalApi\ExternalApiRequestException;
use App\Service\Domain\CurrencyPairService;
use App\Service\Domain\CurrencyService;
use App\Service\Domain\ExchangeRateService;
use App\Trait\CommandCurrencyUtilsTrait;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Throwable;

#[AsCommand(
    name: 'app:exchange-rate:sync',
    description: 'Set exchange rate for two currencies',
)]
class ExchangeRateSyncCommand extends AbstractCommand
{
    use CommandCurrencyUtilsTrait;

    public function __construct(
        private readonly CurrencyService        $currencyService,
        private readonly CurrencyPairService    $pairService,
        private readonly ExchangeRateService    $rateService,
        private readonly EntityManagerInterface $em,
        private readonly LoggerInterface        $logger
    )
    {
        parent::__construct($this->logger);
    }

    protected function configure(): void
    {
        $this
            ->addArgument(
                name: Argument::FROM->value,
                mode: InputArgument::OPTIONAL,
                description: 'Currency code (e.g. PHP)',
            )
            ->addArgument(
                name: Argument::TO->value,
                mode: InputArgument::OPTIONAL,
                description: 'Currency code (e.g. PHP)',
            );
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @param SymfonyStyle $io
     * @return void
     * @throws DuplicateCurrencyPairException
     * @throws Throwable
     * @throws ExternalApiRequestException
     * @throws CurrencyApiUnavailableException
     * @throws CurrencyDataNotFoundException
     * @throws CurrencyApiExchangeRateNotFoundException
     * @throws CurrencyPairNotFoundException
     * @throws CurrencyNotFoundException
     * @throws DuplicateCurrencyCodeException
     * @throws DuplicateExchangeRateException
     * @throws ExchangeRateNotFoundException
     * @throws ExceptionInterface
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    protected function process(InputInterface $input, OutputInterface $output, SymfonyStyle $io): void
    {
        $io->title('Synchronization of exchange rate');

        $this->em->beginTransaction();

        try {
            $from = $this->getCurrency(Argument::FROM, $input, $io);
            $to = $this->getCurrency(Argument::TO, $input, $io);

            $pair = $this->getCurrencyPair($from, $to, $io);

            $exchangeRate = $this->rateService->get($pair);

            if (is_null($exchangeRate)) {
                $this->rateService->create($pair);
            } else {
                $this->rateService->sync($exchangeRate);
            }

            $this->em->commit();

            $io->success(sprintf(
                "%s-%s exchange rate has been synced successfully!",
                $from->getCode(),
                $to->getCode()
            ));
        } catch (Throwable $e) {
            $this->em->rollback();
            $this->em->close();
            throw $e;
        }
    }

    /**
     * @throws DuplicateCurrencyPairException
     */
    private function getCurrencyPair(Currency $from, Currency $to, SymfonyStyle $io): CurrencyPair
    {
        $pair = $this->pairService->get($from, $to);

        if (is_null($pair)) {
            $fromCode = $from->getCode();
            $toCode = $to->getCode();

            $io->warning(sprintf(
                "Currency pair %s-%s doesn't exist. Will be created",
                $fromCode,
                $toCode
            ));

            $pair = $this->pairService->create($from, $to);

            $io->success(sprintf(
                'Currency pair %s-%s has been created',
                $fromCode,
                $toCode
            ));
        }

        return $pair;
    }

    protected function currencyService(): CurrencyService
    {
        return $this->currencyService;
    }

}
