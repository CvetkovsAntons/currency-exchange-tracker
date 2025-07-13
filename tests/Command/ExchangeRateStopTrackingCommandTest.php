<?php

namespace App\Tests\Command;

use App\Command\ExchangeRateStopTrackingCommand;
use App\Service\Domain\CurrencyPairService;
use App\Tests\Internal\Factory\CurrencyPairTestFactory;
use App\Tests\Internal\Factory\CurrencyTestFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Tester\CommandTester;

class ExchangeRateStopTrackingCommandTest extends TestCase
{
    private CurrencyPairService&MockObject $pairService;
    private ExchangeRateStopTrackingCommand $command;

    protected function setUp(): void
    {
        $this->pairService = $this->createMock(CurrencyPairService::class);
        $logger = $this->createMock(LoggerInterface::class);

        $this->command = $this->getMockBuilder(ExchangeRateStopTrackingCommand::class)
            ->setConstructorArgs([$this->pairService, $logger])
            ->onlyMethods(['getCurrencyPair'])
            ->getMock();
    }

    public function testSuccess(): void
    {
        $from = CurrencyTestFactory::makeEntity('USD');
        $to = CurrencyTestFactory::makeEntity('EUR');

        $pair = CurrencyPairTestFactory::make($from, $to);

        $this->pairService
            ->method('getAllTracked')
            ->willReturn([$pair]);

        $this->pairService
            ->expects($this->once())
            ->method('untrack')
            ->with($pair);

        $tester = new CommandTester($this->command);

        $tester->setInputs(['USD-EUR']);

        $tester->execute([]);

        $output = $tester->getDisplay();

        $this->assertStringContainsString(
            needle: 'Tracking of USD-EUR exchange rate has been stoped successfully!',
            haystack: $output
        );
        $this->assertSame(0, $tester->getStatusCode());
    }

    public function testNoTrackingPairsFound(): void
    {
        $this->pairService
            ->method('getAllTracked')
            ->willReturn([]);

        $tester = new CommandTester($this->command);

        $tester->execute([]);

        $output = $tester->getDisplay();

        $this->assertStringContainsString(
            "Any tracked currency pair has not been found",
            $output
        );
        $this->assertSame(0, $tester->getStatusCode());
    }

}
