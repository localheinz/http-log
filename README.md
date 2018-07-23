# http-log

[![Build Status](https://travis-ci.org/localheinz/http-log.svg?branch=master)](https://travis-ci.org/localheinz/http-log)
[![codecov](https://codecov.io/gh/localheinz/http-log/branch/master/graph/badge.svg)](https://codecov.io/gh/localheinz/http-log)
[![Latest Stable Version](https://poser.pugx.org/localheinz/http-log/v/stable)](https://packagist.org/packages/localheinz/http-log)
[![Total Downloads](https://poser.pugx.org/localheinz/http-log/downloads)](https://packagist.org/packages/localheinz/http-log)

## Installation

Run

```
$ composer global require localheinz/http-log
```

## Usage

Run

```
$ dashboard <path> --alert-threshold=<alert-threshold> --refresh-interval=<refresh-interval>
```

to render a dashboard.

### Arguments

* `path` (optional, defaults to `/var/log/access.log`), path to HTTP access log file

### Options

* `alert-threshold` (optional, defaults to `10`), number of requests per second (an integer) which, when exceeded, triggers an alert
* `refresh-interval` (optional, defaults to `10`), number of seconds (an integer) after which the dashboard will be refreshed

## Demo

Clone this repository.

```
$ git clone git@github.com:localheinz/http-log.git
```

Run

```
$ cd http-log
$ composer install
```

Then run

```
$ php demo/write.php
```

open a separate terminal in the same directory and run

```
$ bin/dashboard
```

## Contributing

Please have a look at [`CONTRIBUTING.md`](.github/CONTRIBUTING.md).

## Code of Conduct

Please have a look at [`CODE_OF_CONDUCT.md`](.github/CODE_OF_CONDUCT.md).
