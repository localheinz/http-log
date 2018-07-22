<?php

declare(strict_types=1);

/**
 * Copyright (c) 2018 Andreas Möller.
 *
 * @see https://github.com/localheinz/http-log
 */

namespace Localheinz\Http\Log;

final class Section implements SectionInterface
{
    /**
     * @var string
     */
    private $value;

    private function __construct(string $value)
    {
        $this->value = $value;
    }

    /**
     * @param string $requestUrl
     *
     * @throws \InvalidArgumentException
     *
     * @return self
     */
    public static function fromRequestUrl(string $requestUrl): self
    {
        if (0 === \preg_match('/^(?P<section>\/[\w\.\-0-9]*)/', $requestUrl, $matches)) {
            throw new \InvalidArgumentException(\sprintf(
                'Request URL "%s" appears to be invalid.',
                $requestUrl
            ));
        }

        return new self($matches['section']);
    }

    public function value(): string
    {
        return $this->value;
    }
}
