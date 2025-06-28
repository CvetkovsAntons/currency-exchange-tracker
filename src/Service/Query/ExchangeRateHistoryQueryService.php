<?php

namespace App\Service\Query;

use App\Dto\ExchangeRateRequest;
use App\Entity\ExchangeRateHistory;
use App\Exception\CurrencyCodeException;
use App\Exception\CurrencyPairException;
use App\Exception\DateTimeInvalidException;
use App\Exception\ExchangeRateException;
use App\Exception\MissingParametersException;
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
     * @return ExchangeRateHistory
     * @throws DateTimeInvalidException
     * @throws ExchangeRateException
     * @throws MissingParametersException
     */
    public function fetch(ExchangeRateRequest $request): ExchangeRateHistory
    {
        if (empty($request->from) || empty($request->to)) {
            throw new MissingParametersException(['from', 'to']);
        }

        $fromCurrency = $this->currencyService->get($request->from);
        if (is_null($fromCurrency)) {
            throw new CurrencyCodeException($request->from, code: 400);
        }

        $toCurrency = $this->currencyService->get($request->to);
        if (is_null($toCurrency)) {
            throw new CurrencyCodeException($request->to, code: 400);
        }

        $currencyPair = $this->pairService->get($fromCurrency, $toCurrency);
        if (is_null($currencyPair)) {
            throw new CurrencyPairException(sprintf(
                "Currency pair %s->%s doesn't exist",
                $fromCurrency->getCode(),
                $toCurrency->getCode()
            ), 400);
        }

        $createdAt = null;
        if (!empty($request->datetime)) {
            try {
                $createdAt = new DateTimeImmutable($request->datetime);
            } catch (Throwable) {
                throw new DateTimeInvalidException();
            }
        }

        $exchangeRate = $this->repository->findClosestBefore($currencyPair, $createdAt);

        if (is_null($exchangeRate)) {
            throw new ExchangeRateException("Couldn't find exchange rate", 404);
        }

        return $exchangeRate;
    }

}
