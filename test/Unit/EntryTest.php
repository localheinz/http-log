<?php

declare(strict_types=1);

/**
 * Copyright (c) 2018 Andreas Möller.
 *
 * @see https://github.com/localheinz/http-log
 */

namespace Localheinz\Http\Log\Test\Unit;

use Localheinz\Http\Log\Entry;
use Localheinz\Http\Log\EntryInterface;
use Localheinz\Test\Util\Helper;
use PHPUnit\Framework;

/**
 * @internal
 */
final class EntryTest extends Framework\TestCase
{
    use Helper;

    public function testImplementsEntryInterface(): void
    {
        $this->assertClassImplementsInterface(EntryInterface::class, Entry::class);
    }

    /**
     * @see \Kassner\LogParser\LogParser::parse()
     */
    public function testFromParsedReturnsEntry(): void
    {
        $dateFormat = 'd/M/Y:H:i:s O';

        $faker = $this->faker();

        $requestTime = $faker->dateTime;

        $parsed = new \stdClass();

        $parsed->host = $faker->ipv4;
        $parsed->logname = $faker->word;
        $parsed->user = $faker->userName;
        $parsed->stamp = $requestTime->getTimestamp();
        $parsed->time = $requestTime->format($dateFormat);
        $parsed->requestMethod = $faker->randomElement([
            'DELETE',
            'GET',
            'OPTIONS',
            'PATCH',
            'POST',
            'PUT',
        ]);
        $parsed->URL = '/' . \implode('/', $faker->words);
        $parsed->requestProtocol = 'HTTP/1.0';
        $parsed->status = $faker->numberBetween(200, 500);
        $parsed->responseBytes = $faker->numberBetween(1000, 5000);

        $entry = Entry::fromParsed($parsed);

        $this->assertInstanceOf(EntryInterface::class, $entry);

        $this->assertSame($parsed->host, $entry->ip());
        $this->assertSame($parsed->logname, $entry->userIdentifier());
        $this->assertSame($parsed->user, $entry->userId());
        $this->assertSame($parsed->time, $entry->requestTime()->format($dateFormat));
        $this->assertSame($parsed->requestMethod, $entry->requestMethod());
        $this->assertSame($parsed->URL, $entry->requestUrl());
        $this->assertSame($parsed->requestProtocol, $entry->requestProtocol());
        $this->assertSame($parsed->status, $entry->responseStatus());
        $this->assertSame($parsed->responseBytes, $entry->responseSize());
    }
}
