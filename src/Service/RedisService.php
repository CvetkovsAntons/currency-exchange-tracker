<?php

namespace App\Service;

use Psr\Cache\InvalidArgumentException;
use Symfony\Component\Cache\Adapter\RedisAdapter;

readonly class RedisService
{
    public function __construct(private RedisAdapter $cache) {}

    /**
     * @throws InvalidArgumentException
     */
    public function set(string $key, mixed $value, int $ttl = 3600): void
    {
        $item = $this->cache->getItem($key);
        $item->set($value);
        $item->expiresAfter($ttl);

        $this->cache->save($item);
    }

    /**
     * @throws InvalidArgumentException
     */
    public function get(string $key): mixed
    {
        $item = $this->cache->getItem($key);

        if (!$item->isHit()) {
            return null;
        }

        return $item->get();
    }

    /**
     * @throws InvalidArgumentException
     */
    public function delete(string $key): bool
    {
        return $this->cache->deleteItem($key);
    }

}
