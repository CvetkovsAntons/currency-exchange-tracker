<?php

namespace App\Tests\Command;

use App\Command\ExchangeRateListCommand;
use App\Entity\Currency;
use App\Entity\CurrencyPair;
use App\Entity\ExchangeRate;
use App\Service\Domain\ExchangeRateService;
use DateTimeImmutable;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Tester\CommandTester;

class ExchangeRateListCommandTest extends TestCase
{
    private ExchangeRateService&MockObject $service;
    private LoggerInterface&MockObject $logger;
    private ExchangeRateListCommand $command;

    protected function setUp(): void
    {
        $this->service = $this->createMock(ExchangeRateService::class);
        $this->logger = $this->createMock(LoggerInterface::class);

        $this->command = new ExchangeRateListCommand($this->service, $this->logger);
    }

    public function testExchangeRateListNotEmpty(): void
    {
        $from = $this->createConfiguredMock(Currency::class, ['getCode' => 'USD']);
        $to = $this->createConfiguredMock(Currency::class, ['getCode' => 'EUR']);

        $pair = $this->createConfiguredMock(CurrencyPair::class, [
            'getFromCurrency' => $from,
            'getToCurrency' => $to,
        ]);

        $rate = $this->createConfiguredMock(ExchangeRate::class, [
            'getCurrencyPair' => $pair,
            'getRate' => '1.23',
            'getUpdatedAt' => new DateTimeImmutable('2025-06-29 12:00:00'),
        ]);

        $this->service
            ->method('getAll')
            ->willReturn([$rate]);

        $tester = new CommandTester($this->command);
        $tester->execute([]);

        $output = $tester->getDisplay();

        $this->assertStringContainsString('Exchange Rate List', $output);
        $this->assertStringContainsString('USD -> EUR', $output);
        $this->assertStringContainsString('1.23', $output);
        $this->assertStringContainsString('2025-06-29 12:00:00', $output);
    }

    public function testExchangeRateListEmpty(): void
    {
        $this->service
            ->method('getAll')
            ->willReturn([]);

        $tester = new CommandTester($this->command);
        $tester->execute([]);

        $output = $tester->getDisplay();

        $this->assertStringContainsString('Exchange Rate List', $output);
        $this->assertStringContainsString('No exchange rates found.', $output);
    }

}
