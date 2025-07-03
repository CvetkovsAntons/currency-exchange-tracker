<?php

namespace App\Tests\Command;

use App\Command\ExchangeRateHistoryListCommand;
use App\Entity\Currency;
use App\Entity\CurrencyPair;
use App\Entity\ExchangeRateHistory;
use App\Service\Domain\ExchangeRateHistoryService;
use DateTimeImmutable;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Tester\CommandTester;

class ExchangeRateHistoryListCommandTest extends TestCase
{
    private ExchangeRateHistoryService&MockObject $service;
    private ExchangeRateHistoryListCommand $command;

    protected function setUp(): void
    {
        $this->service = $this->createMock(ExchangeRateHistoryService::class);
        $logger = $this->createMock(LoggerInterface::class);

        $this->command = new ExchangeRateHistoryListCommand($this->service, $logger);
    }

    public function testExchangeRateHistoryListNotEmpty(): void
    {
        $from = $this->createConfiguredMock(Currency::class, ['getCode' => 'USD']);
        $to = $this->createConfiguredMock(Currency::class, ['getCode' => 'EUR']);

        $pair = $this->createConfiguredMock(CurrencyPair::class, [
            'getFromCurrency' => $from,
            'getToCurrency' => $to,
        ]);

        $history = $this->createConfiguredMock(ExchangeRateHistory::class, [
            'getCurrencyPair' => $pair,
            'getRate' => '1.25',
            'getCreatedAt' => new DateTimeImmutable('2025-06-29 14:00:00'),
        ]);

        $this->service
            ->method('getAll')
            ->willReturn([$history]);

        $tester = new CommandTester($this->command);
        $tester->execute([]);

        $output = $tester->getDisplay();

        $this->assertStringContainsString('Exchange Rate History List', $output);
        $this->assertStringContainsString('USD -> EUR', $output);
        $this->assertStringContainsString('1.25', $output);
        $this->assertStringContainsString('2025-06-29 14:00:00', $output);
    }

    public function testExchangeRateHistoryListEmpty(): void
    {
        $this->service
            ->method('getAll')
            ->willReturn([]);

        $tester = new CommandTester($this->command);
        $tester->execute([]);

        $output = $tester->getDisplay();

        $this->assertStringContainsString('Exchange Rate History List', $output);
        $this->assertStringContainsString('No exchange rates found.', $output);
    }

}
