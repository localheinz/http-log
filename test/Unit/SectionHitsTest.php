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
     * @dataProvider providerInvalidSection
     *
     * @param string $section
     */
    public function testConstructorRejectsInvalidSectionName(string $section): void
    {
        $hits = $this->faker()->numberBetween(1);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Section needs to be an non-empty and non-blank string.');

        new SectionHits(
            $section,
            $hits
        );
    }

    public function providerInvalidSection(): \Generator
    {
        $values = [
            'string-empty' => '',
            'string-blank' => '  ',
        ];

        foreach ($values as $key => $value) {
            yield $key => [
                $value,
            ];
        }
    }

    /**
     * @dataProvider providerInvalidHits
     *
     * @param int $hits
     */
    public function testConstructorRejectsInvalidHits(int $hits): void
    {
        $section = $this->faker()->word;

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(\sprintf(
            'Hits needs to be an integer equal to or greater than 0, but %d is not.',
            $hits
        ));

        new SectionHits(
            $section,
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
     * @dataProvider providerValidSectionAndHits
     *
     * @param string $section
     * @param int    $hits
     */
    public function testConstructorSetsValues(string $section, int $hits): void
    {
        $sectionHits = new SectionHits(
            $section,
            $hits
        );

        $this->assertSame($section, $sectionHits->section());
        $this->assertSame($hits, $sectionHits->hits());
    }

    public function providerValidSectionAndHits(): \Generator
    {
        $faker = $this->faker();

        $sections = [
            $faker->word,
            '/' . $faker->word,
        ];

        $hits = [
            0,
            1,
            $faker->numberBetween(2),
        ];

        foreach ($sections as $section) {
            foreach ($hits as $hit) {
                yield [
                    $section,
                    $hit,
                ];
            }
        }
    }

    public function testConstructorTrimsSection(): void
    {
        $faker = $this->faker();

        $section = ' ' . $faker->word . ' ';

        $sectionHits = new SectionHits(
            $section,
            $faker->numberBetween(1)
        );

        $this->assertSame(\trim($section), $sectionHits->section());
    }
}
