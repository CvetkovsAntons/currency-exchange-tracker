<?php

namespace App\Service\Query;

use App\Dto\ExchangeRateRequest;
use App\Entity\ExchangeRateHistory;
use App\Exception\DateTime\DateTimeInvalidFormatException;
use App\Exception\ExchangeRate\ExchangeRateNotFoundException;
use App\Exception\Request\MissingParametersException;
use App\Service\Domain\CurrencyPairService;
use App\Service\Domain\CurrencyService;
use App\Service\Domain\ExchangeRateHistoryService;
use DateTimeImmutable;
use Throwable;

/**
 * Service was created for handling hard queries from controllers and commands
 */
readonly class ExchangeRateHistoryQueryService
{
    public function __construct(
        private ExchangeRateHistoryService    $historyService,
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
    public function getLatestExchangeRate(ExchangeRateRequest $request): ?ExchangeRateHistory
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

        $from = $this->currencyService->get($request->from);
        $to = $this->currencyService->get($request->to);

        if (is_null($from) || is_null($to)) {
            return null;
        }

        $pair = $this->pairService->get($from, $to);

        if (is_null($pair)) {
            return null;
        }

        $exchangeRate = $this->historyService->getLatest($pair, $createdAt);

        if (is_null($exchangeRate)) {
            throw new ExchangeRateNotFoundException($from->getCode(), $to->getCode());
        }

        return $exchangeRate;
    }

}
