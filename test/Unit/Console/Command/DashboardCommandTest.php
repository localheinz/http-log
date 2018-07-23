<?php

declare(strict_types=1);

/**
 * Copyright (c) 2018 Andreas Möller.
 *
 * @see https://github.com/localheinz/http-log
 */

namespace Localheinz\Http\Log\Test\Unit\Console\Command;

use Localheinz\Http\Log\Console\Command\DashboardCommand;
use Localheinz\Test\Util\Helper;
use PHPUnit\Framework;
use Symfony\Component\Console;

/**
 * @internal
 */
final class DashboardCommandTest extends Framework\TestCase
{
    use Helper;

    public function testExtendsConsoleCommand(): void
    {
        $this->assertClassExtends(Console\Command\Command::class, DashboardCommand::class);
    }

    public function testHasNameAndDescription(): void
    {
        $command = new DashboardCommand();

        $this->assertSame('dashboard', $command->getName());
        $this->assertSame('Shows a dashboard with information about an HTTP access log file', $command->getDescription());
    }

    public function testHasPathArgument(): void
    {
        $command = new DashboardCommand();

        $inputDefinition = $command->getDefinition();

        $this->assertTrue($inputDefinition->hasArgument('path'));

        $argument = $inputDefinition->getArgument('path');

        $this->assertFalse($argument->isRequired());
        $this->assertSame('Path to the log file', $argument->getDescription());
        $this->assertSame('/var/log/access.log', $argument->getDefault());
    }

    public function testHasAlertThresholdOption(): void
    {
        $command = new DashboardCommand();

        $inputDefinition = $command->getDefinition();

        $this->assertTrue($inputDefinition->hasOption('alert-threshold'));

        $option = $inputDefinition->getOption('alert-threshold');

        $this->assertNull($option->getShortcut());
        $this->assertTrue($option->isValueRequired());
        $this->assertSame('Alert threshold (in requests/second)', $option->getDescription());
        $this->assertSame(10, $option->getDefault());
    }

    public function testHasRefreshIntervalOption(): void
    {
        $command = new DashboardCommand();

        $inputDefinition = $command->getDefinition();

        $this->assertTrue($inputDefinition->hasOption('refresh-interval'));

        $option = $inputDefinition->getOption('refresh-interval');

        $this->assertNull($option->getShortcut());
        $this->assertTrue($option->isValueRequired());
        $this->assertSame('Refresh interval (in seconds)', $option->getDescription());
        $this->assertSame(10, $option->getDefault());
    }

    public function testExecuteFailsWhenLogFileDoesNotExist(): void
    {
        $path = 'non-existent-file.log';

        $command = new DashboardCommand();

        $tester = new Console\Tester\CommandTester($command);

        $tester->execute([
            'path' => $path,
        ]);

        $this->assertSame(1, $tester->getStatusCode());

        $expected = \sprintf(
            'Log file at %s does not exist',
            $path
        );

        $this->assertContains($expected, $tester->getDisplay());
    }

    /**
     * @dataProvider providerInvalidAlertThreshold
     *
     * @param int $alertThreshold
     */
    public function testExecuteFailsWhenAlertThresholdIsTooSmall(int $alertThreshold): void
    {
        $command = new DashboardCommand();

        $tester = new Console\Tester\CommandTester($command);

        $tester->execute([
            'path' => __FILE__,
            '--alert-threshold' => $alertThreshold,
        ]);

        $this->assertSame(1, $tester->getStatusCode());

        $expected = \sprintf(
            'Alert threshold (in requests/second) needs to be greater than 0, but %d is not.',
            $alertThreshold
        );

        $this->assertContains($expected, $tester->getDisplay());
    }

    public function providerInvalidAlertThreshold(): \Generator
    {
        $values = [
            'int-zero' => 0,
            'int-below-zero' => -1 * $this->faker()->numberBetween(1),
        ];

        foreach ($values as $key => $value) {
            yield $key => [
                $value,
            ];
        }
    }

    /**
     * @dataProvider providerInvalidRefreshInterval
     *
     * @param int $refreshInterval
     */
    public function testExecuteFailsWhenRefreshIntervalIsInvalid(int $refreshInterval): void
    {
        $command = new DashboardCommand();

        $tester = new Console\Tester\CommandTester($command);

        $tester->execute([
            'path' => __FILE__,
            '--refresh-interval' => $refreshInterval,
        ]);

        $this->assertSame(1, $tester->getStatusCode());

        $expected = \sprintf(
            'Refresh interval (in seconds) needs to be greater than 0, but %d is not.',
            $refreshInterval
        );

        $this->assertContains($expected, $tester->getDisplay());
    }

    public function providerInvalidRefreshInterval(): \Generator
    {
        $values = [
            'int-zero' => 0,
            'int-below-zero' => -1 * $this->faker()->numberBetween(1),
        ];

        foreach ($values as $key => $value) {
            yield $key => [
                $value,
            ];
        }
    }
}
