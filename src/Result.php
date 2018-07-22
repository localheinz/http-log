<?php

declare(strict_types=1);

/**
 * Copyright (c) 2018 Andreas Möller.
 *
 * @see https://github.com/localheinz/http-log
 */

namespace Localheinz\Http\Log;

final class Result implements ResultInterface
{
    /**
     * @var SectionHitsInterface[]
     */
    private $sectionHits;

    public function __construct(SectionHitsInterface ...$sectionHits)
    {
        $this->sectionHits = $sectionHits;
    }

    public function sectionHits(): array
    {
        return $this->sectionHits;
    }
}
