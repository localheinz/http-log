<?php

declare(strict_types=1);

/**
 * Copyright (c) 2018 Andreas Möller.
 *
 * @see https://github.com/localheinz/http-log
 */

namespace Localheinz\Http\Log\Test\Unit\Exception;

use Localheinz\Http\Log\Exception\UnableToParseEntryException;
use Localheinz\Test\Util\Helper;
use PHPUnit\Framework;

/**
 * @internal
 */
final class UnableToParseEntryExceptionTest extends Framework\TestCase
{
    use Helper;

    public function testExtendsInvalidArgumentException(): void
    {
        $this->assertClassExtends(\InvalidArgumentException::class, UnableToParseEntryException::class);
    }

    public function testFromLineAndPatternCreatesException(): void
    {
        $faker = $this->faker();

        $line = $faker->sentence;
        $pattern = $faker->sentence;

        $exception = UnableToParseEntryException::fromLineAndPattern(
            $line,
            $pattern
        );

        $this->assertInstanceOf(UnableToParseEntryException::class, $exception);

        $expected = \sprintf(
            'Unable to parse log line "%s", it does not match the pattern "%s".',
            $line,
            $pattern
        );

        $this->assertSame($expected, $exception->getMessage());
    }
}
