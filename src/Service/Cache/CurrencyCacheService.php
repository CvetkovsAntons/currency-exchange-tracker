<?php

namespace App\Service\Cache;

use App\Dto\Currency;
use App\Service\RedisService;
use Psr\Cache\InvalidArgumentException;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

readonly class CurrencyCacheService
{
    public function __construct(
        private RedisService          $redis,
        private DenormalizerInterface $denormalizer,
    ) {}

    /**
     * @param Currency $dto
     * @param int $ttl
     * @return void
     * @throws InvalidArgumentException
     */
    public function setCurrency(Currency $dto, int $ttl = 3600): void
    {
        $this->redis->set($this->key($dto->code), $dto->toArray(), $ttl);
    }

    /**
     * @param Currency[] $currencies
     * @param int $ttl
     * @return void
     * @throws InvalidArgumentException
     */
    public function setCurrencies(array $currencies, int $ttl = 3600): void
    {
        foreach ($currencies as $currency) {
            $this->setCurrency($currency, $ttl);
        }
    }

    /**
     * @param string $currencyCode
     * @return Currency|null
     * @throws InvalidArgumentException
     * @throws ExceptionInterface
     */
    public function getCurrency(string $currencyCode): ?Currency
    {
        $currency = $this->redis->get($this->key($currencyCode));

        if (empty($currency)) {
            return null;
        }

        return $this->denormalizer->denormalize($currency, Currency::class);
    }

    /**
     * @param array $currencyCodes
     * @return Currency[]
     * @throws InvalidArgumentException
     * @throws ExceptionInterface
     */
    public function getCurrencies(array $currencyCodes): array
    {
        $currencies = [];

        foreach ($currencyCodes as $currencyCode) {
            $currencies[$currencyCode] = $this->getCurrency($currencyCode);
        }

        return $currencies;
    }

    /**
     * @param string $currencyCode
     * @return bool
     * @throws InvalidArgumentException
     */
    public function delete(string $currencyCode): bool
    {
        return $this->redis->delete($this->key($currencyCode));
    }

    private function key(string $currencyCode): string
    {
        return sprintf('currency_%s', strtolower($currencyCode));
    }

}
