<?php

declare(strict_types=1);

/**
 * Copyright (c) 2018 Andreas Möller.
 *
 * @see https://github.com/localheinz/http-log
 */

namespace Localheinz\Http\Log;

interface AnalyzerInterface
{
    /**
     * @param LogInterface       $log
     * @param \DateTimeImmutable $since
     *
     * @throws \InvalidArgumentException
     *
     * @return SectionHitsInterface[]
     */
    public function sectionHits(LogInterface $log, \DateTimeImmutable $since): array;

    /**
     * @param LogInterface       $log
     * @param \DateTimeImmutable $since
     *
     * @throws \InvalidArgumentException
     *
     * @return float
     */
    public function requestsPerSecond(LogInterface $log, \DateTimeImmutable $since): float;
}
