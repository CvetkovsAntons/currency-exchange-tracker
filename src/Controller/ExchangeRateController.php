<?php

namespace App\Controller;

use App\Dto\ExchangeRateRequest;
use App\Exception\CurrencyCodeException;
use App\Exception\CurrencyPairException;
use App\Exception\DateTimeInvalidException;
use App\Exception\ExchangeRateException;
use App\Exception\MissingParametersException;
use App\Service\Query\ExchangeRateHistoryQueryService;
use DateTimeInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Throwable;

class ExchangeRateController extends AbstractController
{
    public function __construct(
        #[Autowire(service: 'monolog.logger.api')]
        private readonly LoggerInterface     $logger,
        private readonly DenormalizerInterface $serializer,
    ) {}

    #[Route('/exchange-rate/', methods: ['GET'])]
    public function getRate(
        Request                         $request,
        ExchangeRateHistoryQueryService $service,
    ): JsonResponse
    {
        try {
            $request = $this->serializer->denormalize(
                data: $request->query->all(),
                type: ExchangeRateRequest::class,
                format: 'json'
            );

            $exchangeRate = $service->getClosestExchangeRate($request);

            return $this->json([
                'from' => $request->from,
                'to' => $request->to,
                'rate' => $exchangeRate->getRate(),
                'datetime' => $exchangeRate->getCreatedAt()->format('Y-m-d H:i:s'),
            ]);
        } catch (MissingParametersException|CurrencyCodeException|CurrencyPairException|DateTimeInvalidException|ExchangeRateException $e) {
            return $this->json(['error' => $e->getMessage()], $e->getCode());
        } catch (Throwable $e) {
            $this->logger->error($e);
            return $this->json(['error' => 'Internal server error'], 500);
        }
    }

}
