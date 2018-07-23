<?php

declare(strict_types=1);

/**
 * Copyright (c) 2018 Andreas Möller.
 *
 * @see https://github.com/localheinz/http-log
 */

namespace Localheinz\Http\Log\Console\Command;

use Localheinz\Clock;
use Localheinz\Http\Log;
use React\EventLoop;
use React\Filesystem;
use Symfony\Component\Console;

final class DashboardCommand extends Console\Command\Command
{
    private const ALERT_THRESHOLD_DEFAULT = 10;
    private const ALERT_THRESHOLD_MIN = 0;

    private const PATH_DEFAULT = '/var/log/access.log';

    private const REFRESH_INTERVAL_DEFAULT = 10;
    private const REFRESH_INTERVAL_MIN = 0;

    private const LOG_MAX_AGE = 7200;
    private const LOG_REFRESH_INTERVAL = 5;

    /**
     * @var EventLoop\LoopInterface
     */
    private $loop;

    /**
     * @var Filesystem\FilesystemInterface
     */
    private $filesystem;

    /**
     * @var Log\EntryParserInterface
     */
    private $entryParser;

    /**
     * @var Clock\ClockInterface
     */
    private $clock;

    /**
     * @var Log\AnalyzerInterface
     */
    private $analyzer;

    public function __construct()
    {
        parent::__construct();

        $this->loop = EventLoop\Factory::create();
        $this->filesystem = Filesystem\Filesystem::create($this->loop);
        $this->entryParser = new Log\EntryParser();
        $this->clock = new Clock\SystemClock();
        $this->analyzer = new Log\Analyzer();
    }

    protected function configure(): void
    {
        $this
            ->setName('dashboard')
            ->setDescription('Shows a dashboard with information about an HTTP access log file')
            ->addArgument(
                'path',
                Console\Input\InputArgument::OPTIONAL,
                'Path to the log file',
                self::PATH_DEFAULT
            )
            ->addOption(
                'alert-threshold',
                null,
                Console\Input\InputOption::VALUE_REQUIRED,
                'Alert threshold (in requests/second)',
                self::ALERT_THRESHOLD_DEFAULT
            )
            ->addOption(
                'refresh-interval',
                null,
                Console\Input\InputOption::VALUE_REQUIRED,
                'Refresh interval (in seconds)',
                self::REFRESH_INTERVAL_DEFAULT
            );
    }

    protected function execute(Console\Input\InputInterface $input, Console\Output\OutputInterface $output): int
    {
        $io = new Console\Style\SymfonyStyle(
            $input,
            $output
        );

        $io->title('Localheinz HTTP Log Dashboard');

        $path = $input->getArgument('path');

        if (!\file_exists($path)) {
            $io->error(\sprintf(
                'Log file at %s does not exist',
                $path
            ));

            return 1;
        }

        $alertThreshold = $input->getOption('alert-threshold');

        if (self::ALERT_THRESHOLD_MIN >= $alertThreshold) {
            $io->error(\sprintf(
                'Alert threshold (in requests/second) needs to be greater than %d, but %d is not.',
                self::ALERT_THRESHOLD_MIN,
                $alertThreshold
            ));

            return 1;
        }

        $refreshInterval = (int) $input->getOption('refresh-interval');

        if (self::REFRESH_INTERVAL_MIN >= $refreshInterval) {
            $io->error(\sprintf(
                'Refresh interval (in seconds) needs to be greater than %d, but %d is not.',
                self::REFRESH_INTERVAL_MIN,
                $refreshInterval
            ));

            return 1;
        }

        $io->listing([
            \sprintf(
                'observing <info>%s</info>',
                $path
            ),
            \sprintf(
                'alerting when threshold of <info>%s</info> requests/second is exceeded',
                $alertThreshold
            ),
            \sprintf(
                'refreshing dashboard every <info>%s</info> second(s)',
                $refreshInterval
            ),
        ]);

        /** @var Console\Output\ConsoleOutputInterface $output */
        $section = $output->section();

        $log = new Log\ExpiringLog(self::LOG_MAX_AGE);

        $this->loop->addPeriodicTimer($refreshInterval, function () use ($section, $log, $refreshInterval, $alertThreshold) {
            $this->renderDashboard(
                $section,
                $log,
                $refreshInterval,
                $alertThreshold
            );
        });

        $this->filesystem->getContents($path)->then(function (string $content) use ($section, $path, $log, $refreshInterval, $alertThreshold) {
            $this->addEntries(
                $log,
                $content
            );

            $this->renderDashboard(
                $section,
                $log,
                $refreshInterval,
                $alertThreshold
            );

            /** @var int $lastSize */
            $lastSize = \mb_strlen($content);

            $file = $this->filesystem->file($path);

            $file->open('r')->then(function (Filesystem\Stream\GenericStreamInterface $stream) use ($log, $file, &$lastSize, $refreshInterval) {
                $fileDescriptor = $stream->getFiledescriptor();

                $logRefreshInterval = \min(
                    $refreshInterval,
                    self::LOG_REFRESH_INTERVAL
                );

                $this->loop->addPeriodicTimer($logRefreshInterval, function () use ($log, $file, &$lastSize, $fileDescriptor) {
                    $file->size()->then(function (int $size) use ($log, $fileDescriptor, &$lastSize) {
                        if ($lastSize === $size) {
                            return;
                        }

                        $this->filesystem->getAdapter()
                            ->read($fileDescriptor, $size - $lastSize, $lastSize)
                            ->then(function (string $content) use ($log) {
                                $this->addEntries(
                                    $log,
                                    $content
                                );
                            });

                        $lastSize = $size;
                    });
                });
            });
        });

        $this->loop->run();

        return 0;
    }

    private function addEntries(Log\LogInterface $log, string $content): void
    {
        foreach (\explode(\PHP_EOL, $content) as $line) {
            if ('' === \trim($line)) {
                continue;
            }

            try {
                $parsed = $this->entryParser->parse($line);
            } catch (Log\Exception\UnableToParseEntryException $exception) {
                continue;
            }

            $entry = Log\Entry::fromParsed($parsed);

            $log->log($entry);
        }
    }

    private function renderDashboard(
        Console\Output\ConsoleSectionOutput $output,
        Log\LogInterface $log,
        int $refreshInterval,
        int $alertThreshold
    ): void {
        $now = $this->clock->now();

        $sectionHits = $this->analyzer->sectionHits(
            $log,
            $now->sub(new \DateInterval(\sprintf(
                'PT%dS',
                $refreshInterval
            )))
        );

        \usort($sectionHits, function (Log\SectionHitsInterface $a, Log\SectionHitsInterface $b) {
            return $b->hits() <=> $a->hits();
        });

        $sectionHitsSelected = \array_slice(
            $sectionHits,
            0,
            10
        );

        $requestsPerSecond = $this->analyzer->requestsPerSecond(
            $log,
            $now->sub(new \DateInterval(\sprintf(
                'PT%dS',
                self::LOG_MAX_AGE
            )))
        );

        $output->clear();

        $this->renderSectionHits(
            $output,
            $sectionHitsSelected
        );

        $this->renderRequestsPerSecond(
            $output,
            $requestsPerSecond,
            $alertThreshold
        );
    }

    private function renderSectionHits(Console\Output\ConsoleSectionOutput $output, array $sectionHits): void
    {
        $table = new Console\Helper\Table($output);

        $table
            ->setStyle('box')
            ->setHeaders([
                'Section',
                'Hits',
            ])
            ->setRows(\array_map(function (Log\SectionHitsInterface $sectionHits) {
                return [
                    $sectionHits->section()->value(),
                    $sectionHits->hits(),
                ];
            }, $sectionHits));

        $table->render();
    }

    private function renderRequestsPerSecond(Console\Output\ConsoleSectionOutput $output, float $requestsPerSecond, int $alertThreshold): void
    {
        static $lastAlert = null;

        $table = new Console\Helper\Table($output);

        $table
            ->setStyle('box')
            ->setHeaders([
                'Requests per second',
            ])
            ->setRows([
                [
                    \round($requestsPerSecond, 3),
                ],
            ]);

        $table->render();

        if ($requestsPerSecond > $alertThreshold) {
            if (null === $lastAlert) {
                $lastAlert = $this->clock->now();
            }

            $output->writeln(\sprintf(
                '<error>Alert threshold of %s requests/second exceeded at %s.</error>',
                $alertThreshold,
                $this->clock->now()->format('Y-m-d H:i:s')
            ));

            return;
        }

        if ($requestsPerSecond <= $alertThreshold && null !== $lastAlert) {
            $lastAlert = null;

            $output->writeln(\sprintf(
                '<info>Alert threshold of %s requests/second recovered at %s.</info>',
                $alertThreshold,
                $this->clock->now()->format('Y-m-d H:i:s')
            ));
        }
    }
}
