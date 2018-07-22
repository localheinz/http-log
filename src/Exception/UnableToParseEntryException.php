<?php

declare(strict_types=1);

/**
 * Copyright (c) 2018 Andreas Möller.
 *
 * @see https://github.com/localheinz/http-log
 */

namespace Localheinz\Http\Log\Exception;

final class UnableToParseEntryException extends \InvalidArgumentException
{
    public static function fromLineAndPattern(string $line, string $pattern): self
    {
        return new self(\sprintf(
            'Unable to parse log line "%s", it does not match the pattern "%s".',
            $line,
            $pattern
        ));
    }
}
