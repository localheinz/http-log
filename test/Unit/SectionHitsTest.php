<?php

declare(strict_types=1);

/**
 * Copyright (c) 2018 Andreas Möller.
 *
 * @see https://github.com/localheinz/http-log
 */

namespace Localheinz\Http\Log\Test\Unit;

use Localheinz\Http\Log\SectionHits;
use Localheinz\Http\Log\SectionHitsInterface;
use Localheinz\Http\Log\SectionInterface;
use Localheinz\Test\Util\Helper;
use PHPUnit\Framework;

/**
 * @internal
 */
final class SectionHitsTest extends Framework\TestCase
{
    use Helper;

    public function testImplementsSectionHitsInterface(): void
    {
        $this->assertClassImplementsInterface(SectionHitsInterface::class, SectionHits::class);
    }

    /**
     * @dataProvider providerInvalidHits
     *
     * @param int $hits
     */
    public function testConstructorRejectsInvalidHits(int $hits): void
    {
        $section = $this->prophesize(SectionInterface::class);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(\sprintf(
            'Hits needs to be an integer equal to or greater than 0, but %d is not.',
            $hits
        ));

        new SectionHits(
            $section->reveal(),
            $hits
        );
    }

    public function providerInvalidHits(): \Generator
    {
        $values = [
            'int-minus-one' => -1,
            'int-less-than-minus-one' => -1 * $this->faker()->numberBetween(2),
        ];

        foreach ($values as $key => $value) {
            yield $key => [
                $value,
            ];
        }
    }

    /**
     * @dataProvider providerValidHits
     *
     * @param int $hits
     */
    public function testConstructorSetsValues(int $hits): void
    {
        $section = $this->prophesize(SectionInterface::class);

        $sectionHits = new SectionHits(
            $section->reveal(),
            $hits
        );

        $this->assertSame($section->reveal(), $sectionHits->section());
        $this->assertSame($hits, $sectionHits->hits());
    }

    public function providerValidHits(): \Generator
    {
        $values = [
            'int-zero' => 0,
            'int-one' => 1,
            'int-greater-than-one' => $this->faker()->numberBetween(2),
        ];

        foreach ($values as $key => $value) {
            yield $key => [
                $value,
            ];
        }
    }
}
