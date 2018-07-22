<?php

declare(strict_types=1);

/**
 * Copyright (c) 2018 Andreas Möller.
 *
 * @see https://github.com/localheinz/http-log
 */

namespace Localheinz\Http\Log;

interface EntryParserInterface
{
    /**
     * @param string $line
     *
     * @throws Exception\UnableToParseEntryException
     *
     * @return \stdClass
     */
    public function parse(string $line): \stdClass;
}
