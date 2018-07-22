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
     * @return string
     */
    public function section(): string;

    /**
     * Returns the number of hits for this section.
     *
     * @return int
     */
    public function hits(): int;
}
