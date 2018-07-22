<?php

declare(strict_types=1);

/**
 * Copyright (c) 2018 Andreas Möller.
 *
 * @see https://github.com/localheinz/http-log
 */

namespace Localheinz\Http\Log\Test\Unit;

use Kassner\LogParser;
use Localheinz\Http\Log\EntryParser;
use Localheinz\Http\Log\EntryParserInterface;
use Localheinz\Http\Log\Exception;
use Localheinz\Test\Util\Helper;
use PHPUnit\Framework;
use Prophecy\Argument;

/**
 * @internal
 */
final class EntryParserTest extends Framework\TestCase
{
    use Helper;

    public function testImplementsEntryParserInterface(): void
    {
        $this->assertClassImplementsInterface(EntryParserInterface::class, EntryParser::class);
    }

    public function testConstructorSetsPattern(): void
    {
        $logParser = $this->prophesize(LogParser\LogParser::class);

        $logParser
            ->setFormat(Argument::is('%h %l %u %t "%m %U %H" %>s %b'))
            ->shouldBeCalled();

        new EntryParser($logParser->reveal());
    }

    public function testParseThrowsUnableToParseExceptionWhenLineDoesNotMatch(): void
    {
        $faker = $this->faker();

        $line = $faker->sentence;
        $pattern = $faker->sentence;

        $logParser = $this->prophesize(LogParser\LogParser::class);

        $logParser
            ->setFormat(Argument::type('string'))
            ->shouldBeCalled();

        $logParser
            ->parse(Argument::is($line))
            ->shouldBeCalled()
            ->willThrow(new LogParser\FormatException());

        $logParser
            ->getPCRE()
            ->shouldBeCalled()
            ->willReturn($pattern);

        $entryParser = new EntryParser($logParser->reveal());

        $this->expectException(Exception\UnableToParseEntryException::class);

        $entryParser->parse($line);
    }

    public function testParseReturnsParsedLogEntry(): void
    {
        $line = $this->faker()->sentence;
        $parsed = new \stdClass();

        $logParser = $this->prophesize(LogParser\LogParser::class);

        $logParser
            ->setFormat(Argument::type('string'))
            ->shouldBeCalled();

        $logParser
            ->parse(Argument::is($line))
            ->shouldBeCalled()
            ->willReturn($parsed);

        $entryParser = new EntryParser($logParser->reveal());

        $this->assertSame($parsed, $entryParser->parse($line));
    }
}
