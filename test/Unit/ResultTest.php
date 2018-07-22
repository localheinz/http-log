<?php

declare(strict_types=1);

/**
 * Copyright (c) 2018 Andreas Möller.
 *
 * @see https://github.com/localheinz/http-log
 */

namespace Localheinz\Http\Log\Test\Unit;

use Localheinz\Http\Log\Result;
use Localheinz\Http\Log\ResultInterface;
use Localheinz\Http\Log\SectionHitsInterface;
use Localheinz\Test\Util\Helper;
use PHPUnit\Framework;

/**
 * @internal
 */
final class ResultTest extends Framework\TestCase
{
    use Helper;

    public function testImplementsResultInterface(): void
    {
        $this->assertClassImplementsInterface(ResultInterface::class, Result::class);
    }

    public function testDefaults(): void
    {
        $result = new Result();

        $this->assertEmpty($result->sectionHits());
    }

    public function testConstructorSetsSectionHits(): void
    {
        $sectionHits = \array_map(function () {
            return $this->prophesize(SectionHitsInterface::class)->reveal();
        }, \range(1, 5));

        $result = new Result(...$sectionHits);

        $this->assertSame(\array_values($sectionHits), $result->sectionHits());
    }
}
