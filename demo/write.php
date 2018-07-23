<?php

declare(strict_types=1);

/**
 * Copyright (c) 2018 Andreas Möller.
 *
 * @see https://github.com/localheinz/http-log
 */
require_once __DIR__ . '/../vendor/autoload.php';

use Faker\Factory;
use React\EventLoop;
use React\Filesystem;
use React\Stream;

$path = __DIR__ . '/../var/log/access.log';

$faker = Factory::create();
$loop = EventLoop\Factory::create();

$filesystem = Filesystem\Filesystem::create($loop);

/**
 * Some fake data.
 */
$sections = $faker->words(10);

$urls = \array_reduce(
    $sections,
    function (array $urls, string $section) use ($faker) {
        $urls[] = '/' . $section;

        $depth = $faker->numberBetween(0, 3);

        for ($i = 0; $i < $depth; ++$i) {
            $urls[] = '/' . $section . '/' . \implode('/', $faker->words($depth));
        }

        return $urls;
    },
    []
);

$ips = \array_map(function () use ($faker) {
    return $faker->ipv4;
}, \range(1, 50));

$userNames = \array_combine(
    $ips,
    \array_map(function () use ($faker) {
        $numberOfUsersOnIp = $faker->numberBetween(1, 20);

        return \array_map(function () use ($faker) {
            return $faker->unique()->userName;
        }, \range(1, $numberOfUsersOnIp));
    }, \range(1, \count($ips)))
);

$filesystem
    ->file($path)
    ->open('cw')
    ->then(function (Stream\WritableStreamInterface $stream) use ($loop, $faker, $ips, $userNames, $urls) {
        $loop->addPeriodicTimer(0.2, function () use ($stream, $faker, $ips, $userNames, $urls) {
            $entryCount = $faker->numberBetween(0, 5);

            for ($i = 0; $i < $entryCount; ++$i) {
                $now = new \DateTimeImmutable();

                $ip = $faker->randomElement($ips);
                $userName = $faker->randomElement($userNames[$ip]);
                $url = $faker->randomElement($urls);
                $httpRequestMethod = $faker->randomElement([
                    'DELETE',
                    'GET',
                    'OPTIONS',
                    'PATCH',
                    'POST',
                    'PUT',
                ]);
                $httpResponseStatus = $faker->randomElement([
                    200,
                    400,
                    403,
                    404,
                ]);
                $httpResponseSize = $faker->numberBetween(500, 5000);

                $line = \sprintf(
                    '%s - %s [%s] "%s %s HTTP/1.0" %d %d' . \PHP_EOL,
                    $ip,
                    $userName,
                    $now->format('d/M/Y:H:i:s O'),
                    $httpRequestMethod,
                    $url,
                    $httpResponseStatus,
                    $httpResponseSize
                );

                $stream->write($line);

                echo $line;
            }
        });
    });

$loop->run();
