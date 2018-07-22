<?php

declare(strict_types=1);

/**
 * Copyright (c) 2018 Andreas Möller.
 *
 * @see https://github.com/localheinz/http-log
 */

namespace Localheinz\Http\Log;

interface SectionHitsInterface
{
    /**
     * Returns the name of the section.
     *
     * @return SectionInterface
     */
    public function section(): SectionInterface;

    /**
     * Returns the number of hits for this section.
     *
     * @return int
     */
    public function hits(): int;

    /**
     * Returns a cloned of the current section with an additional hit added.
     *
     * @return SectionHitsInterface
     */
    public function withAdditionalHit(): self;
}
