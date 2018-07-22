<?php

declare(strict_types=1);

/**
 * Copyright (c) 2018 Andreas Möller.
 *
 * @see https://github.com/localheinz/http-log
 */

namespace Localheinz\Http\Log\Test\Unit;

use Localheinz\Clock;
use Localheinz\Http\Log\EntryInterface;
use Localheinz\Http\Log\ExpiringLog;
use Localheinz\Test\Util\Helper;
use PHPUnit\Framework;

/**
 * @internal
 */
final class ExpiringLogTest extends Framework\TestCase
{
    use Helper;

    public function testDefaults(): void
    {
        $log = new ExpiringLog();

        $this->assertEmpty($log->entries());
    }

    /**
     * @dataProvider providerInvalidMaxAgeInSeconds
     *
     * @param int $maxAgeInSeconds
     */
    public function testConstructorRejectsInvalidMaxAgeInSeconds(int $maxAgeInSeconds): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(\sprintf(
            'Max age needs to be greater than 0, but %d is not.',
            $maxAgeInSeconds
        ));

        new ExpiringLog($maxAgeInSeconds);
    }

    public function providerInvalidMaxAgeInSeconds(): \Generator
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

    public function testEntriesDoesNotReturnExpiredEntries(): void
    {
        $faker = $this->faker();

        $maxAgeInSeconds = $faker->numberBetween(
            5,
            3600
        );

        $now = new \DateTimeImmutable();

        $expiredEntry = $this->prophesize(EntryInterface::class);

        $expiredEntry
            ->requestTime()
            ->shouldBeCalled()
            ->willReturn($now->sub(new \DateInterval(\sprintf(
                'PT%dS',
                $faker->numberBetween(
                    $maxAgeInSeconds + 1,
                    $maxAgeInSeconds + 100
                )
            ))));

        $clock = new Clock\FrozenClock($now);

        $log = new ExpiringLog(
            $maxAgeInSeconds,
            $clock
        );

        $log->log($expiredEntry->reveal());

        $this->assertEmpty($log->entries());
    }

    public function testEntriesReturnsNonExpiredEntries(): void
    {
        $faker = $this->faker();

        $maxAgeInSeconds = $faker->numberBetween(
            5,
            3600
        );

        $now = new \DateTimeImmutable();

        $nonExpiredEntry = $this->prophesize(EntryInterface::class);

        $nonExpiredEntry
            ->requestTime()
            ->shouldBeCalled()
            ->willReturn($now->sub(new \DateInterval(\sprintf(
                'PT%dS',
                $faker->numberBetween(
                    0,
                    $maxAgeInSeconds - 1
                )
            ))));

        $clock = new Clock\FrozenClock($now);

        $log = new ExpiringLog(
            $maxAgeInSeconds,
            $clock
        );

        $log->log($nonExpiredEntry->reveal());

        $this->assertContains($nonExpiredEntry->reveal(), $log->entries());
    }
}
