<?php

namespace App\Controller;

use App\Exception\Api\MissingParametersException;
use App\Exception\CurrencyCodeException;
use App\Exception\CurrencyPairException;
use App\Exception\DateTimeInvalidException;
use App\Exception\ExchangeRateException;
use App\Service\Domain\CurrencyPairService;
use App\Service\Domain\CurrencyService;
use App\Service\Domain\ExchangeRateHistoryService;
use DateTimeImmutable;
use DateTimeInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Throwable;

class ExchangeRateController extends AbstractController
{
    public function __construct(
        #[Autowire(service: 'monolog.logger.api')]
        private readonly LoggerInterface $logger,
    ) {}

    #[Route('/exchange-rate/', methods: ['GET'])]
    public function getRate(
        Request                    $request,
        CurrencyService            $currencyService,
        CurrencyPairService        $pairService,
        ExchangeRateHistoryService $historyService,
    ): JsonResponse
    {
        try {
            $fromCurrencyCode = $request->get('from');
            $toCurrencyCode = $request->get('to');

            if (!$fromCurrencyCode || !$toCurrencyCode) {
                throw new MissingParametersException(['from', 'to']);
            }

            $fromCurrency = $currencyService->get($fromCurrencyCode);
            if (is_null($fromCurrency)) {
                throw new CurrencyCodeException($fromCurrencyCode, code: 400);
            }

            $toCurrency = $currencyService->get($toCurrencyCode);
            if (is_null($toCurrency)) {
                throw new CurrencyCodeException($toCurrencyCode, code: 400);
            }

            $currencyPair = $pairService->get($fromCurrency, $toCurrency);
            if (is_null($currencyPair)) {
                throw new CurrencyPairException(sprintf(
                    "Currency pair %s->%s doesn't exist",
                    $fromCurrency->getCode(),
                    $toCurrency->getCode()
                ), 400);
            }

            $datetime = $request->get('datetime');

            $createdAt = null;
            if (!empty($datetime)) {
                try {
                    $createdAt = new DateTimeImmutable($datetime);
                } catch (Throwable) {
                    throw new DateTimeInvalidException();
                }
            }

            $rate = $historyService->get($currencyPair, $createdAt);

            if (is_null($rate)) {
                throw new ExchangeRateException("Couldn't find exchange rate", 404);
            }

            return $this->json([
                'from' => $fromCurrency->getCode(),
                'to' => $toCurrency->getCode(),
                'rate' => $rate->getRate(),
                'datetime' => $rate->getCreatedAt()->format(DateTimeInterface::ATOM),
            ]);
        } catch (MissingParametersException|CurrencyCodeException|CurrencyPairException|DateTimeInvalidException|ExchangeRateException $e) {
            return $this->json(['error' => $e->getMessage()], $e->getCode());
        } catch (Throwable $e) {
            $this->logger->error($e);
            return $this->json(['error' => 'Internal server error'], 500);
        }
    }

}
