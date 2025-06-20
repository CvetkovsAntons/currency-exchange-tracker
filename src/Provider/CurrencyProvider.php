<?php

namespace App\Provider;

use App\Dto\Currency;
use App\Exception\CurrencyApiException;
use App\Service\CurrencyApiService;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

readonly class CurrencyProvider
{
    public function __construct(
        private CurrencyApiService  $apiService,
        private SerializerInterface $serializer,
    ) {}

    /**
     * @param string ...$code
     * @return CUrrency[]|null
     * @throws CurrencyApiException
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function getCurrencies(string ...$code): ?array
    {
        $response = $this->apiService->currencies(...$code);

        return array_map(
            callback: fn($v) => $this->serializer->denormalize($v, Currency::class),
            array: $response->toArray()['data'] ?? [],
        );
    }

    /**
     * @param string $code
     * @return Currency|null
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function getCurrency(string $code): ?Currency
    {
        $currencies = $this->getCurrencies($code);

        return $currencies[$code] ?? null;
    }

}
