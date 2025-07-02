<?php

namespace App\Service\Query;

use App\Dto\ExchangeRateRequest;
use App\Entity\ExchangeRateHistory;
use App\Exception\DateTime\DateTimeInvalidFormatException;
use App\Exception\ExchangeRate\ExchangeRateNotFoundException;
use App\Exception\Request\MissingParametersException;
use App\Repository\ExchangeRateHistoryRepository;
use App\Service\Domain\CurrencyPairService;
use App\Service\Domain\CurrencyService;
use DateTimeImmutable;
use Throwable;

readonly class ExchangeRateHistoryQueryService
{
    public function __construct(
        private ExchangeRateHistoryRepository $repository,
        private CurrencyService               $currencyService,
        private CurrencyPairService           $pairService,
    ) {}

    /**
     * @param ExchangeRateRequest $request
     * @return ExchangeRateHistory|null
     * @throws DateTimeInvalidFormatException
     * @throws MissingParametersException
     * @throws ExchangeRateNotFoundException
     */
    public function getClosestExchangeRate(ExchangeRateRequest $request): ?ExchangeRateHistory
    {
        if (empty($request->from) || empty($request->to)) {
            throw new MissingParametersException(['from', 'to']);
        }

        $createdAt = null;
        if (!empty($request->datetime)) {
            try {
                $createdAt = new DateTimeImmutable($request->datetime);
            } catch (Throwable) {
                throw new DateTimeInvalidFormatException();
            }
        }

        $fromCurrency = $this->currencyService->get($request->from);
        $toCurrency = $this->currencyService->get($request->to);
        if (is_null($fromCurrency) || is_null($toCurrency)) {
            return null;
        }

        $currencyPair = $this->pairService->get($fromCurrency, $toCurrency);
        if (is_null($currencyPair)) {
            return null;
        }

        $exchangeRate = $this->repository->findClosest($currencyPair, $createdAt);

        if (is_null($exchangeRate)) {
            throw new ExchangeRateNotFoundException($fromCurrency->getCode(), $toCurrency->getCode());
        }

        return $exchangeRate;
    }

}
