<?php

namespace App\Tests\Command;

use App\Command\CurrencyListCommand;
use App\Entity\Currency;
use App\Service\Domain\CurrencyService;
use App\Tests\Utils\Factory\CurrencyPairTestFactory;
use App\Tests\Utils\Factory\CurrencyTestFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Tester\CommandTester;

class CurrencyListCommandTest extends TestCase
{
    private CurrencyService&MockObject $service;
    private LoggerInterface&MockObject $logger;
    private CurrencyListCommand $command;

    protected function setUp(): void
    {
        $this->service = $this->createMock(CurrencyService::class);
        $this->logger = $this->createMock(LoggerInterface::class);

        $this->command = new CurrencyListCommand($this->service, $this->logger);
    }

    public function testCurrencyListNotEmpty(): void
    {
        $from = CurrencyTestFactory::create();
        $to = CurrencyTestFactory::create('EUR');

        $currencies = [
            $this->createConfiguredMock(Currency::class, [
                'getCode' => 'USD',
                'getName' => 'US Dollar',
                'getSymbol' => '$',
            ]),
            $this->createConfiguredMock(Currency::class, [
                'getCode' => 'EUR',
                'getName' => 'Euro',
                'getSymbol' => '€',
            ])
        ];

        $this->service
            ->method('getAll')
            ->willReturn($currencies);

        $tester = new CommandTester($this->command);

        $tester->execute([]);

        $output = $tester->getDisplay();

        $this->assertStringContainsString('Currency List', $output);
        $this->assertStringContainsString('USD', $output);
        $this->assertStringContainsString('EUR', $output);
        $this->assertStringContainsString('US Dollar', $output);
        $this->assertStringContainsString('€', $output);
    }

    public function testCurrencyListEmpty(): void
    {
        $this->service
            ->method('getAll')
            ->willReturn([]);

        $tester = new CommandTester($this->command);

        $tester->execute([]);

        $output = $tester->getDisplay();

        $this->assertStringContainsString('Currency List', $output);
        $this->assertStringContainsString('No currencies found.', $output);
    }

}
