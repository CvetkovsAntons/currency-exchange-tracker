<?php

namespace App\Scheduler\Handler;

use App\Scheduler\Message\ExchangeRateSyncMessage;
use App\Service\Domain\ExchangeRateService;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Throwable;

#[AsMessageHandler]
readonly class ExchangeRateSyncMessageHandler
{
    public function __construct(
        private ExchangeRateService $service,
        private LoggerInterface     $logger,
    ) {}

    public function __invoke(ExchangeRateSyncMessage $message): void
    {
        try {
            $this->service->syncAll();
        } catch (Throwable $e) {
            $this->logger->error($e->getMessage());
        }
    }

}
