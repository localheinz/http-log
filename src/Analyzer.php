<?php

declare(strict_types=1);

/**
 * Copyright (c) 2018 Andreas Möller.
 *
 * @see https://github.com/localheinz/http-log
 */

namespace Localheinz\Http\Log;

use Localheinz\Clock;

final class Analyzer implements AnalyzerInterface
{
    /**
     * @var Clock\ClockInterface
     */
    private $clock;

    public function __construct(Clock\ClockInterface $clock = null)
    {
        $this->clock = $clock ?: new Clock\SystemClock();
    }

    public function sectionHits(LogInterface $log, \DateTimeImmutable $since): array
    {
        $now = $this->clock->now();

        if ($since->getTimestamp() >= $now->getTimestamp()) {
            throw new \InvalidArgumentException('Since needs to be in the past.');
        }

        /** @var SectionHitsInterface[] $sectionHits */
        $sectionHits = [];

        foreach ($log->entries() as $entry) {
            if ($entry->requestTime() < $since) {
                continue;
            }

            try {
                $section = Section::fromRequestUrl($entry->requestUrl());
            } catch (\InvalidArgumentException $exception) {
                continue;
            }

            $key = $section->value();

            if (!\array_key_exists($key, $sectionHits)) {
                $sectionHits[$key] = new SectionHits($section);

                continue;
            }

            $sectionHits[$key] = $sectionHits[$key]->withAdditionalHit();
        }

        return \array_values($sectionHits);
    }

    public function requestsPerSecond(LogInterface $log, \DateTimeImmutable $since): float
    {
        $now = $this->clock->now();

        if ($since->getTimestamp() >= $now->getTimestamp()) {
            throw new \InvalidArgumentException('Since needs to be in the past.');
        }

        $requests = \array_filter($log->entries(), function (EntryInterface $entry) use ($since) {
            return $entry->requestTime() >= $since;
        });

        if (0 === \count($requests)) {
            return 0.0;
        }

        /** @var EntryInterface $oldestRequest */
        $oldestRequest = \reset($requests);

        $oldestTimestamp = \max(
            $oldestRequest->requestTime()->getTimestamp(),
            $since->getTimestamp()
        );

        $seconds = $now->getTimestamp() - $oldestTimestamp;

        return \count($requests) / $seconds;
    }
}
