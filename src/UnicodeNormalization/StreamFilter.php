<?php

/*
 * This file is part of Unicode Normalization Stream Filter.
 *
 * (c) Stephan Jorek <stephnan.jorek@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sjorek\UnicodeNormalization;

/**
 * @author Stephan Jorek <stephan.jorek@gmail.com>
 */
class StreamFilter extends \php_user_filter
{
    /**
     * @var integer
     */
    public $form;

    const BYTES_1 = 0b10000000; // 0b0xxxxxxx;
    const PAYLOAD = 0b11000000; // 0b10xxxxxx;
    const BYTES_2 = 0b11100000; // 0b110xxxxx;
    const BYTES_3 = 0b11110000; // 0b1110xxxx;
    const BYTES_4 = 0b11111000; // 0b11110xxx;

    public function filter($in, $out, &$consumed, $closing)
    {
        while ($bucket = stream_bucket_make_writeable($in)) {
            $payload = 0;
            foreach(range(-1, -4) as $offset) {
                if (abs($offset) > $bucket->datalen) {
                    $offset = 0;
                    break;
                }
                $input = $bucket->data[$bucket->datalen + $offset];
                if (self::BYTES_1 > $input) {
                    if ($offset === -1 && $payload === 0) {
                        $offset = 0;
                    }
                    $payload = 0;
                    break;
                } elseif(self::PAYLOAD > $input) {
                    if ($offset > -4) {
                        $payload += 1;
                        continue;
                    }
                    break;
                } elseif(self::BYTES_2 > $input) {
                    if ($offset === -2 && $payload === 1) {
                        $offset = 0;
                    }
                    $payload = 0;
                    break;
                } elseif(self::BYTES_3 > $input) {
                    if ($offset === -3 && $payload === 2) {
                        $offset = 0;
                    }
                    break;
                } elseif(self::BYTES_4 > $input) {
                    if ($offset === -4 && $payload === 3) {
                        $offset = 0;
                    }
                    $payload = 0;
                    break;
                }
            }
            $input = $offset === 0 ? $bucket->data : substr($bucket->data, 0, $offset);
            $bucket->data = \Normalizer::normalize($input, $this->form);
            $consumed += $bucket->datalen + $offset;
            stream_bucket_append($out, $bucket);
        }

        return PSFS_PASS_ON;
    }

    public function onCreate()
    {
        list($namespace, $filter) = explode('.', $this->filtername, 2);
        $filter = strtoupper($filter);
        if ($namespace === static::$namespace && in_array($filter, array('NONE', 'NFC', 'NFD', 'NFKC', 'NFKD'), true) && defined('Normalizer::' . $filter)) {
            $this->form = constant('Normalizer::' . $filter);
            return true;
        }
        /* Some other normalize.* filter was asked for,
        report failure so that PHP will keep looking */
        return false;
    }

    /**
     * @var string|null
     */
    protected static $namespace = null;

    /**
     * @param  string $namespace
     * @return bool
     */
    public static function register($namespace = 'normalize')
    {
        // already registered or missing dependency ?
        if (static::$namespace !== null || !class_exists('Normalizer', true)) {
            return false;
        }

        $result = stream_filter_register(sprintf('%s.*', $namespace), static::class);
        if ($result === true) {
            static::$namespace = $namespace;
        }

        return $result;
    }
}
