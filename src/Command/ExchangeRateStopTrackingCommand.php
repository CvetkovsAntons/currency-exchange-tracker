<?php

namespace App\Command;

use App\Enum\Argument;
use App\Exception\Currency\CurrencyNotFoundException;
use App\Exception\Currency\DuplicateCurrencyCodeException;
use App\Exception\CurrencyApi\CurrencyApiUnavailableException;
use App\Exception\CurrencyApi\CurrencyDataNotFoundException;
use App\Exception\CurrencyPair\CurrencyPairNotFoundException;
use App\Exception\ExternalApi\ExternalApiRequestException;
use App\Service\Domain\CurrencyPairService;
use App\Service\Domain\CurrencyService;
use App\Service\Domain\ExchangeRateService;
use App\Trait\CommandCurrencyUtilsTrait;
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
    name: 'app:exchange-rate:stop-tracking',
    description: 'Stop track of exchange rate for two currencies',
)]
class ExchangeRateStopTrackingCommand extends AbstractCommand
{
    use CommandCurrencyUtilsTrait;

    public function __construct(
        private readonly CurrencyService        $currencyService,
        private readonly CurrencyPairService    $pairService,
        private readonly ExchangeRateService    $rateService,
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
     * @throws ClientExceptionInterface
     * @throws CurrencyPairNotFoundException
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws Throwable
     * @throws TransportExceptionInterface
     * @throws ExternalApiRequestException
     * @throws CurrencyApiUnavailableException
     * @throws CurrencyDataNotFoundException
     * @throws CurrencyNotFoundException
     * @throws DuplicateCurrencyCodeException
     * @throws ExceptionInterface
     */
    protected function process(InputInterface $input, OutputInterface $output, SymfonyStyle $io): void
    {
        $io->title('Stop track of currencies exchange rate');

        $from = $this->getCurrency(Argument::FROM, $input, $io, false);
        $to = $this->getCurrency(Argument::TO, $input, $io, false);

        $pair = $this->pairService->get($from, $to);

        if (is_null($pair)) {
            throw new CurrencyPairNotFoundException($from->getCode(), $to->getCode());
        }

        $this->pairService->untrack($pair);

        $io->success(sprintf(
            "Tracking of %s-%s exchange rate has been stoped successfully!",
            $from->getCode(),
            $to->getCode()
        ));
    }

    protected function currencyService(): CurrencyService
    {
        return $this->currencyService;
    }

}
