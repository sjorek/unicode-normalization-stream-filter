# [unicode-normalization stream-filter](https://sjorek.github.io/unicode-normalization-stream-filter/) for [composer](http://getcomposer.org)

[![Build Status](https://travis-ci.org/sjorek/unicode-normalization-stream-filter.svg?branch=master)](https://travis-ci.org/sjorek/unicode-normalization-stream-filter)
[![Dependency Status](https://gemnasium.com/badges/github.com/sjorek/unicode-normalization-stream-filter.svg)](https://gemnasium.com/github.com/sjorek/unicode-normalization-stream-filter)

A [composer](http://getcomposer.org)-package providing a unicode-normalization stream-filter.


## Installation

```bash
php composer.phar require sjorek/unicode-normalization-stream-filter
```


## Example

```php
<?php
// Not required if the file was autoloaded (e.g. using composer)
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

