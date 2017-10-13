# [unicode-normalization stream-filter](https://sjorek.github.io/unicode-normalization-stream-filter/) for [composer](http://getcomposer.org)

[![Build Status](https://img.shields.io/travis/sjorek/unicode-normalization-stream-filter.svg)](https://travis-ci.org/sjorek/unicode-normalization-stream-filter)
[![Dependency Status](https://img.shields.io/gemnasium/sjorek/unicode-normalization-stream-filter.svg)](https://gemnasium.com/github.com/sjorek/unicode-normalization-stream-filter)
[![Development Dependency Status](https://gemnasium.com/badges/github.com/sjorek/unicode-normalization-stream-filter.svg)](https://gemnasium.com/github.com/sjorek/unicode-normalization-stream-filter)
[![GitHub issues](https://img.shields.io/github/issues/sjorek/unicode-normalization-stream-filter.svg)](https://github.com/sjorek/unicode-normalization-stream-filter/issues)
[![Packagist version](https://img.shields.io/packagist/v/sjorek/unicode-normalization-stream-filter.svg)](https://packagist.org/packages/sjorek/unicode-normalization-stream-filter)
[![Packagist downloads](https://img.shields.io/packagist/dt/sjorek/unicode-normalization-stream-filter.svg)](https://packagist.org/packages/sjorek/unicode-normalization-stream-filter/stats)
[![3-Clause BSD License](https://img.shields.io/badge/license-New%20BSD-blue.svg)](https://github.com/sjorek/unicode-normalization-stream-filter/blob/master/LICENSE)

[![GitHub forks](https://img.shields.io/github/forks/sjorek/unicode-normalization-stream-filter.svg?style=social)](https://github.com/sjorek/unicode-normalization-stream-filter/network)
[![GitHub stars](https://img.shields.io/github/stars/sjorek/unicode-normalization-stream-filter.svg?style=social)](https://github.com/sjorek/unicode-normalization-stream-filter/stargazers)
[![GitHub watchers](https://img.shields.io/github/watchers/sjorek/unicode-normalization-stream-filter.svg?style=social)](https://github.com/sjorek/unicode-normalization-stream-filter/watchers)
[![Twitter](https://img.shields.io/twitter/url/https/github.com/sjorek/unicode-normalization-stream-filter.svg?style=social)](https://twitter.com/intent/tweet?url=https%3A%2F%2Fsjorek.github.io%2Funicode-normalization-stream-filter%2F)

A [composer](http://getcomposer.org)-package providing a unicode-normalization stream-filter.


## Installation

```bash
php composer.phar require sjorek/unicode-normalization-stream-filter
```


## Example

```php
<?php
\Sjorek\UnicodeNormalization\StreamFilter::register();

$in_file = fopen('utf8-file.txt', 'r');
$out_file = fopen('utf8-normalized-to-nfc-file.txt', 'w');

// It works as a read filter:
stream_filter_append($in_file, 'convert.unicode-normalization.NFC');
// And it also works as a write filter:
// stream_filter_append($out_file, 'convert.unicode-normalization.NFC');

stream_copy_to_stream($in_file, $out_file);
```


## Usage

```php
<?php
/**
 * resource   $stream        The stream to filter.
 * string     $form          The form to normalize unicode to.
 * int        $read_write    STREAM_FILTER_* constants to override the filter injection point
 * @link http://php.net/manual/en/function.stream-filter-append.php
 */
stream_filter_append($stream, "convert.unicode-normalization.$form", $read_write);
```

Note: Be careful when using on streams in 'r+' or 'w+' (or similar) modes; by default PHP will assign the
filter to both the reading and writing chain. This means it will attempt to convert the data twice - first when
reading from the stream, and once again when writing to it.


## Contributing

Look at the [contribution guidelines](CONTRIBUTING.md)

