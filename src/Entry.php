<?php

declare(strict_types=1);

/**
 * Copyright (c) 2018 Andreas Möller.
 *
 * @see https://github.com/localheinz/http-log
 */

namespace Localheinz\Http\Log;

final class Entry implements EntryInterface
{
    /**
     * @var string
     */
    private $ip;

    /**
     * @var string
     */
    private $userIdentifier;

    /**
     * @var string
     */
    private $userId;

    /**
     * @var \DateTimeImmutable
     */
    private $requestTime;

    /**
     * @var string
     */
    private $requestMethod;

    /**
     * @var string
     */
    private $requestProtocol;

    /**
     * @var string
     */
    private $requestUrl;

    /**
     * @var int
     */
    private $responseStatus;

    /**
     * @var int
     */
    private $responseSize;

    public function ip(): string
    {
        return $this->ip;
    }

    public function userIdentifier(): string
    {
        return $this->userIdentifier;
    }

    public function userId(): string
    {
        return $this->userId;
    }

    public function requestTime(): \DateTimeImmutable
    {
        return $this->requestTime;
    }

    public function requestMethod(): string
    {
        return $this->requestMethod;
    }

    public function requestProtocol(): string
    {
        return $this->requestProtocol;
    }

    public function requestUrl(): string
    {
        return $this->requestUrl;
    }

    public function responseStatus(): int
    {
        return $this->responseStatus;
    }

    public function responseSize(): int
    {
        return $this->responseSize;
    }

    public static function fromParsed(\stdClass $parsed): EntryInterface
    {
        $entry = new self();

        $entry->ip = $parsed->host;
        $entry->userIdentifier = $parsed->logname;
        $entry->userId = $parsed->user;
        $entry->requestTime = new \DateTimeImmutable($parsed->time);
        $entry->requestMethod = $parsed->requestMethod;
        $entry->requestUrl = $parsed->URL;
        $entry->requestProtocol = $parsed->requestProtocol;
        $entry->responseStatus = $parsed->status;
        $entry->responseSize = $parsed->responseBytes;

        return $entry;
    }
}
