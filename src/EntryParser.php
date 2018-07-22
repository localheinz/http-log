<?php

declare(strict_types=1);

/**
 * Copyright (c) 2018 Andreas Möller.
 *
 * @see https://github.com/localheinz/http-log
 */

namespace Localheinz\Http\Log;

use Kassner\LogParser;

final class EntryParser implements EntryParserInterface
{
    private const FORMAT = '%h %l %u %t "%m %U %H" %>s %b';

    /**
     * @var LogParser\LogParser
     */
    private $logParser;

    public function __construct(LogParser\LogParser $logParser = null)
    {
        $this->logParser = $logParser ?: new LogParser\LogParser();
        $this->logParser->setFormat(self::FORMAT);
    }

    public function parse(string $line): \stdClass
    {
        try {
            $parsed = $this->logParser->parse($line);
        } catch (LogParser\FormatException $exception) {
            throw Exception\UnableToParseEntryException::fromLineAndPattern(
                $line,
                $this->logParser->getPCRE()
            );
        }

        return $parsed;
    }
}
