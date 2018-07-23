<?php

declare(strict_types=1);

/**
 * Copyright (c) 2018 Andreas Möller.
 *
 * @see https://github.com/localheinz/http-log
 */

namespace Localheinz\Http\Log\Test\Unit;

use Localheinz\Clock;
use Localheinz\Http\Log\Analyzer;
use Localheinz\Http\Log\AnalyzerInterface;
use Localheinz\Http\Log\EntryInterface;
use Localheinz\Http\Log\LogInterface;
use Localheinz\Http\Log\SectionHitsInterface;
use Localheinz\Test\Util\Helper;
use PHPUnit\Framework;

/**
 * @internal
 */
final class AnalyzerTest extends Framework\TestCase
{
    use Helper;

    public function testImplementsAnalyzerInterface(): void
    {
        $this->assertClassImplementsInterface(AnalyzerInterface::class, Analyzer::class);
    }

    /**
     * @dataProvider providerNowAndInvalidSince
     *
     * @param \DateTimeImmutable $now
     * @param \DateTimeImmutable $since
     */
    public function testSectionHitsRejectsInvalidSince(\DateTimeImmutable $now, \DateTimeImmutable $since): void
    {
        $log = $this->prophesize(LogInterface::class);

        $analyzer = new Analyzer(new Clock\FrozenClock($now));

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Since needs to be in the past.');

        $analyzer->sectionHits(
            $log->reveal(),
            $since
        );
    }

    public function providerNowAndInvalidSince(): \Generator
    {
        $now = new \DateTimeImmutable();

        $values = [
            'now' => $now,
            'in-one-second' => $now->add(new \DateInterval('PT1S')),
        ];

        foreach ($values as $key => $value) {
            yield $key => [
                $now,
                $value,
            ];
        }
    }

    public function testSectionHitsReturnsEmptyArrayWhenLogHasNoEntries(): void
    {
        $log = $this->prophesize(LogInterface::class);

        $log
            ->entries()
            ->shouldBeCalled()
            ->willReturn([]);

        $since = new \DateTimeImmutable('-2 minutes');
        $analyzer = new Analyzer();

        $sectionHits = $analyzer->sectionHits(
            $log->reveal(),
            $since
        );

        $this->assertInternalType('array', $sectionHits);
        $this->assertEmpty($sectionHits);
    }

    public function testSectionHitsIgnoresEntriesWhenRequestTimeIsOlderThanSince(): void
    {
        $faker = $this->faker();

        $since = new \DateTimeImmutable('-30 seconds');

        $entries = \array_map(function () use ($faker, $since) {
            $requestTime = $since->sub(new \DateInterval(\sprintf(
                'PT%dS',
                $faker->numberBetween(1)
            )));

            $entry = $this->prophesize(EntryInterface::class);

            $entry
                ->requestTime()
                ->shouldBeCalled()
                ->willReturn($requestTime);

            return $entry;
        }, \range(0, 10));

        $log = $this->prophesize(LogInterface::class);

        $log
            ->entries()
            ->shouldBeCalled()
            ->willReturn($entries);

        $analyzer = new Analyzer();

        $sectionHits = $analyzer->sectionHits(
            $log->reveal(),
            $since
        );

        $this->assertInternalType('array', $sectionHits);
        $this->assertEmpty($sectionHits);
    }

    public function testSectionHitsIgnoresEntriesWhenRequestUrlCannotBeParsed(): void
    {
        $faker = $this->faker();

        $since = new \DateTimeImmutable('-30 seconds');

        $requestTime = $since->add(new \DateInterval(\sprintf(
            'PT%dS',
            $faker->numberBetween(1, 20)
        )));

        $requestUrl = $faker->sentence;

        $entry = $this->prophesize(EntryInterface::class);

        $entry
            ->requestTime()
            ->shouldBeCalled()
            ->willReturn($requestTime);

        $entry
            ->requestUrl()
            ->shouldBeCalled()
            ->willReturn($requestUrl);

        $entries = [
            $entry,
        ];

        $log = $this->prophesize(LogInterface::class);

        $log
            ->entries()
            ->shouldBeCalled()
            ->willReturn($entries);

        $analyzer = new Analyzer();

        $sectionHits = $analyzer->sectionHits(
            $log->reveal(),
            $since
        );

        $this->assertInternalType('array', $sectionHits);
        $this->assertEmpty($sectionHits);
    }

    public function testSectionHitsAggregatesSectionHitsBySection(): void
    {
        $faker = $this->faker();

        $since = new \DateTimeImmutable('-30 seconds');

        $sectionCount = $faker->numberBetween(2, 10);

        $sections = \array_map(function () use ($faker) {
            return '/' . $faker->unique()->word;
        }, \range(1, $sectionCount));

        $hitsPerSection = \array_combine(
            $sections,
            \array_map(function () use ($faker) {
                return $faker->numberBetween(5, 15);
            }, \range(1, $sectionCount))
        );

        $entries = [];

        foreach ($hitsPerSection as $section => $hits) {
            for ($i = 0; $i < $hits; ++$i) {
                $requestTime = $since->add(new \DateInterval(\sprintf(
                    'PT%dS',
                    $faker->numberBetween(1, 20)
                )));

                $requestUrl = $section;

                $depth = $faker->numberBetween(1, 3);

                if (1 < $depth) {
                    $requestUrl .= '/' . \implode('/', $faker->words($depth - 1));
                }

                $entry = $this->prophesize(EntryInterface::class);

                $entry
                    ->requestTime()
                    ->shouldBeCalled()
                    ->willReturn($requestTime);

                $entry
                    ->requestUrl()
                    ->shouldBeCalled()
                    ->willReturn($requestUrl);

                $entries[] = $entry->reveal();
            }
        }

        $log = $this->prophesize(LogInterface::class);

        $log
            ->entries()
            ->shouldBeCalled()
            ->willReturn($entries);

        $analyzer = new Analyzer();

        $sectionHits = $analyzer->sectionHits(
            $log->reveal(),
            $since
        );

        $this->assertInternalType('array', $sectionHits);
        $this->assertCount($sectionCount, $sectionHits);

        $actual = \array_combine(
            \array_map(function (SectionHitsInterface $sectionHits) {
                return $sectionHits->section()->value();
            }, $sectionHits),
            \array_map(function (SectionHitsInterface $sectionHits) {
                return $sectionHits->hits();
            }, $sectionHits)
        );

        $this->assertEquals($hitsPerSection, $actual);
    }

    /**
     * @dataProvider providerNowAndInvalidSince
     *
     * @param \DateTimeImmutable $now
     * @param \DateTimeImmutable $since
     */
    public function testRequestsPerSecondRejectsInvalidSince(\DateTimeImmutable $now, \DateTimeImmutable $since): void
    {
        $log = $this->prophesize(LogInterface::class);

        $analyzer = new Analyzer(new Clock\FrozenClock($now));

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Since needs to be in the past.');

        $analyzer->requestsPerSecond(
            $log->reveal(),
            $since
        );
    }

    public function testRequestPerSecondReturnsZeroWhenLogHasNoEntries(): void
    {
        $log = $this->prophesize(LogInterface::class);

        $log
            ->entries()
            ->shouldBeCalled()
            ->willReturn([]);

        $since = new \DateTimeImmutable('-2 minutes');
        $analyzer = new Analyzer();

        $requestsPerSecond = $analyzer->requestsPerSecond(
            $log->reveal(),
            $since
        );

        $this->assertSame(0.0, $requestsPerSecond);
    }

    public function testRequestsPerSecondIgnoresEntriesWhenRequestTimeIsOlderThanSince(): void
    {
        $faker = $this->faker();

        $since = new \DateTimeImmutable('-30 seconds');

        $entries = \array_map(function () use ($faker, $since) {
            $requestTime = $since->sub(new \DateInterval(\sprintf(
                'PT%dS',
                $faker->numberBetween(1)
            )));

            $entry = $this->prophesize(EntryInterface::class);

            $entry
                ->requestTime()
                ->shouldBeCalled()
                ->willReturn($requestTime);

            return $entry;
        }, \range(0, 10));

        $log = $this->prophesize(LogInterface::class);

        $log
            ->entries()
            ->shouldBeCalled()
            ->willReturn($entries);

        $analyzer = new Analyzer();

        $requestsPerSecond = $analyzer->requestsPerSecond(
            $log->reveal(),
            $since
        );

        $this->assertSame(0.0, $requestsPerSecond);
    }

    public function testRequestsPerSecondsReturnsEntriesSinceSinceDividedBySecondsBetweenSinceAndNow(): void
    {
        $faker = $this->faker();

        $now = new \DateTimeImmutable();

        $since = $now->sub(new \DateInterval(\sprintf(
            'PT%dS',
            60
        )));

        $requests = 100;

        $requestTimes = \array_map(function () use ($faker, $since) {
            return $since->add(new \DateInterval(\sprintf(
                'PT%dS',
                $faker->numberBetween(0, 60)
            )));
        }, \range(1, $requests - 1));

        $requestTimes[] = $since;

        \usort($requestTimes, function (\DateTimeImmutable $a, \DateTimeImmutable $b) {
            return $a <=> $b;
        });

        $entries = \array_map(function (\DateTimeImmutable $requestTime) {
            $entry = $this->prophesize(EntryInterface::class);

            $entry
                ->requestTime()
                ->shouldBeCalled()
                ->willReturn($requestTime);

            return $entry;
        }, $requestTimes);

        $log = $this->prophesize(LogInterface::class);

        $log
            ->entries()
            ->shouldBeCalled()
            ->willReturn($entries);

        $analyzer = new Analyzer(new Clock\FrozenClock($now));

        $requestsPerSecond = $analyzer->requestsPerSecond(
            $log->reveal(),
            $since
        );

        $seconds = $now->getTimestamp() - $since->getTimestamp();

        $this->assertEquals($requests / $seconds, $requestsPerSecond);
    }

    public function testRequestsPerSecondsReturnsEntriesSinceSinceDividedBySecondsBetweenOldestRequestAndNow(): void
    {
        $faker = $this->faker();

        $now = new \DateTimeImmutable();

        $since = $now->sub(new \DateInterval(\sprintf(
            'PT%dS',
            60
        )));

        $requests = 100;

        $requestTimes = \array_map(function () use ($faker, $since) {
            return $since->add(new \DateInterval(\sprintf(
                'PT%dS',
                $faker->numberBetween(20, 60)
            )));
        }, \range(1, $requests));

        \usort($requestTimes, function (\DateTimeImmutable $a, \DateTimeImmutable $b) {
            return $a <=> $b;
        });

        $oldestRequestTime = \reset($requestTimes);

        $entries = \array_map(function (\DateTimeImmutable $requestTime) {
            $entry = $this->prophesize(EntryInterface::class);

            $entry
                ->requestTime()
                ->shouldBeCalled()
                ->willReturn($requestTime);

            return $entry;
        }, $requestTimes);

        $log = $this->prophesize(LogInterface::class);

        $log
            ->entries()
            ->shouldBeCalled()
            ->willReturn($entries);

        $analyzer = new Analyzer(new Clock\FrozenClock($now));

        $requestsPerSecond = $analyzer->requestsPerSecond(
            $log->reveal(),
            $since
        );

        $seconds = $now->getTimestamp() - $oldestRequestTime->getTimestamp();

        $this->assertEquals($requests / $seconds, $requestsPerSecond);
    }
}
