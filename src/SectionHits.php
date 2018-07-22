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
     * @var string
     */
    private $section;

    /**
     * @var int
     */
    private $hits;

    /**
     * @param string $section
     * @param int    $hits
     *
     * @throws \InvalidArgumentException
     */
    public function __construct(string $section, int $hits)
    {
        if ('' === \trim($section)) {
            throw new \InvalidArgumentException('Section needs to be an non-empty and non-blank string.');
        }

        if (0 > $hits) {
            throw new \InvalidArgumentException(\sprintf(
                'Hits needs to be an integer equal to or greater than 0, but %d is not.',
                $hits
            ));
        }

        $this->section = \trim($section);
        $this->hits = $hits;
    }

    public function section(): string
    {
        return $this->section;
    }

    public function hits(): int
    {
        return $this->hits;
    }
}
