<?php

declare(strict_types=1);

/**
 * Copyright (c) 2018 Andreas Möller.
 *
 * @see https://github.com/localheinz/http-log
 */

namespace Localheinz\Http\Log;

interface ResultInterface
{
    /**
     * Returns an array of section hits.
     *
     * @return SectionHitsInterface[]
     */
    public function sectionHits(): array;
}
