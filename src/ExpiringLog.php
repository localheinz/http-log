<?php

declare(strict_types=1);

/**
 * Copyright (c) 2018 Andreas Möller.
 *
 * @see https://github.com/localheinz/http-log
 */

namespace Localheinz\Http\Log;

use Localheinz\Clock;

final class ExpiringLog implements LogInterface
{
    private const MAX_AGE_DEFAULT = 10;
    private const MAX_AGE_MIN = 0;

    /**
     * @var int
     */
    private $maxAgeInSeconds;

    /**
     * @var Clock\ClockInterface
     */
    private $clock;

    /**
     * @var EntryInterface[]
     */
    private $entries = [];

    /**
     * @param int                       $maxAgeInSeconds
     * @param null|Clock\ClockInterface $clock
     *
     * @throws \InvalidArgumentException
     */
    public function __construct(int $maxAgeInSeconds = self::MAX_AGE_DEFAULT, Clock\ClockInterface $clock = null)
    {
        if (self::MAX_AGE_MIN >= $maxAgeInSeconds) {
            throw new \InvalidArgumentException(\sprintf(
                'Max age needs to be greater than %d, but %d is not.',
                self::MAX_AGE_MIN,
                $maxAgeInSeconds
            ));
        }

        $this->maxAgeInSeconds = $maxAgeInSeconds;
        $this->clock = $clock ?: new Clock\SystemClock();
    }

    public function log(EntryInterface $entry): void
    {
        $this->entries[] = $entry;
    }

    public function entries(): array
    {
        $this->removeExpiredEntries();

        return $this->entries;
    }

    private function removeExpiredEntries(): void
    {
        $now = $this->clock->now();

        $expirationTime = $now->sub(new \DateInterval(\sprintf(
            'PT%dS',
            $this->maxAgeInSeconds
        )));

        $this->entries = \array_filter($this->entries, function (EntryInterface $entry) use ($expirationTime) {
            return $entry->requestTime() > $expirationTime;
        });
    }
}
