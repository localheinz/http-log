<?php

declare(strict_types=1);

/**
 * Copyright (c) 2018 Andreas Möller.
 *
 * @see https://github.com/localheinz/http-log
 */

namespace Localheinz\Http\Log;

/**
 * @see https://en.wikipedia.org/wiki/Common_Log_Format
 */
interface EntryInterface
{
    /**
     * Returns the IP address of the requester.
     *
     * @return string
     */
    public function ip(): string;

    /**
     * Returns the user identifier.
     *
     * @return string
     */
    public function userIdentifier(): string;

    /**
     * Returns the user id.
     *
     * @return string
     */
    public function userId(): string;

    /**
     * Returns the request time.
     *
     * @return \DateTimeImmutable
     */
    public function requestTime(): \DateTimeImmutable;

    /**
     * Returns the request method.
     *
     * @return string
     */
    public function requestMethod(): string;

    /**
     * Returns the request protocol.
     *
     * @return string
     */
    public function requestProtocol(): string;

    /**
     * Returns the request URL.
     *
     * @return string
     */
    public function requestUrl(): string;

    /**
     * Returns the HTTP status code of the response.
     *
     * @return int
     */
    public function responseStatus(): int;

    /**
     * Returns the size of the response body in bytes.
     *
     * @return int
     */
    public function responseSize(): int;
}
