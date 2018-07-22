<?php

declare(strict_types=1);

/**
 * Copyright (c) 2018 Andreas Möller.
 *
 * @see https://github.com/localheinz/http-log
 */

namespace Localheinz\Http\Log;

final class SectionHits implements SectionHitsInterface
{
    /**
     * @var SectionInterface
     */
    private $section;

    /**
     * @var int
     */
    private $hits;

    /**
     * @param SectionInterface $section
     * @param int              $hits
     *
     * @throws \InvalidArgumentException
     */
    public function __construct(SectionInterface $section, int $hits)
    {
        if (0 > $hits) {
            throw new \InvalidArgumentException(\sprintf(
                'Hits needs to be an integer equal to or greater than 0, but %d is not.',
                $hits
            ));
        }

        $this->section = $section;
        $this->hits = $hits;
    }

    public function section(): SectionInterface
    {
        return $this->section;
    }

    public function hits(): int
    {
        return $this->hits;
    }
}
