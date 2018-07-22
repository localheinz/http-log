<?php

declare(strict_types=1);

/**
 * Copyright (c) 2018 Andreas Möller.
 *
 * @see https://github.com/localheinz/http-log
 */

namespace Localheinz\Http\Log\Test\Unit;

use Localheinz\Http\Log\Section;
use Localheinz\Http\Log\SectionInterface;
use Localheinz\Test\Util\Helper;
use PHPUnit\Framework;

/**
 * @internal
 */
final class SectionTest extends Framework\TestCase
{
    use Helper;

    public function testImplementsSegmentInterface(): void
    {
        $this->assertClassImplementsInterface(SectionInterface::class, Section::class);
    }

    /**
     * @dataProvider providerInvalidRequestUrl
     *
     * @param string $requestUrl
     */
    public function testFromRequestUrlRejectsInvalidRequestUrl(string $requestUrl): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(\sprintf(
            'Request URL "%s" appears to be invalid',
            $requestUrl
        ));

        Section::fromRequestUrl($requestUrl);
    }

    public function providerInvalidRequestUrl(): \Generator
    {
        $values = [
            'string-empty' => '',
            'string-blank' => '  ',
            'string-without-leading-slash' => $this->faker()->word,
        ];

        foreach ($values as $key => $value) {
            yield $key => [
                $value,
            ];
        }
    }

    /**
     * @dataProvider providerRequestUrlAndSection
     *
     * @param string $requestUrl
     * @param string $value
     */
    public function testFromRequestUrlCreatesSection(string $requestUrl, string $value): void
    {
        $section = Section::fromRequestUrl($requestUrl);

        $this->assertInstanceOf(SectionInterface::class, $section);
        $this->assertSame($value, $section->value());
    }

    public function providerRequestUrlAndSection(): \Generator
    {
        $values = [
            '/' => '/',
            '/api' => '/api',
            '/api/user' => '/api',
            '/report' => '/report',
        ];

        foreach ($values as $requestUrl => $section) {
            yield [
                $requestUrl,
                $section,
            ];
        }
    }
}
