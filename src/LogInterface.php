<?php

declare(strict_types=1);

/**
 * Copyright (c) 2018 Andreas Möller.
 *
 * @see https://github.com/localheinz/http-log
 */

namespace Localheinz\Http\Log;

interface LogInterface
{
    /**
     * Logs an entry.
     *
     * @param EntryInterface $entry
     */
    public function log(EntryInterface $entry): void;

    /**
     * Returns all entries currently stored in the log.
     *
     * @return EntryInterface[]
     */
    public function entries(): array;
}
