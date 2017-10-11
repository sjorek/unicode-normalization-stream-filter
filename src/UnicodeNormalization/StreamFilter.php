<?php

/*
 * This file is part of Unicode Normalization Stream Filter.
 *
 * (c) Stephan Jorek <stephan.jorek@gmail.com>
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
     * @var int
     */
    public $form;

    const SINGLE_BYTE = 0b10000000; // = 0x80 for 0b0xxxxxxx
    const PAYLOAD_BYTE = 0b11000000; // = 0xC0 for 0b10xxxxxx
    const DOUBLE_BYTE = 0b11100000; // = 0xE0 for 0b110xxxxx
    const TRIPLE_BYTE = 0b11110000; // = 0xF0 for 0b1110xxxx
    const QUAD_BYTE = 0b11111000; // = 0xF8 for 0b11110xxx
//     const PENTA_BYTE = 0b11111100; // = 0xF8 for 0b111110xx
//     const HEXA_BYTE = 0b11111110; // = 0xF8 for 0b1111110x

    const MASK_BYTE = 0b11111111;

    /**
     * {@inheritDoc}
     * @see \php_user_filter::filter()
     */
    public function filter($in, $out, &$consumed, $closing)
    {
        while ($bucket = stream_bucket_make_writeable($in)) {
            if ($bucket->datalen === 0 || 1 > self::getLengthOfCodePoint($bucket->data[0])) {
                return PSFS_ERR_FATAL;
            }
            $payload = 1;
            foreach (range(1, $this->overlong ? 6 : 4) as $offset) {
                if (abs($offset) > $bucket->datalen) {
                    return PSFS_ERR_FATAL;
                }
                $length = self::getLengthOfCodePoint($bucket->data[$bucket->datalen - $offset]);
                if ($length < 0) {
                    return PSFS_ERR_FATAL;
                } elseif ($length === 0) {
                    ++$payload;
                    continue;
                } elseif ($length === $offset && $length === $payload) {
                    $data = $this->normalize($bucket->data);
                    $datalen = $bucket->datalen;
                } else {
                    $data = $this->normalize(substr($bucket->data, 0, -1 * $offset));
                    $datalen = ($bucket->datalen - $offset);
                }
                if ($data === false) {
                    return PSFS_ERR_FATAL;
                }
                $bucket->data = $data;
                $consumed += $datalen;
                stream_bucket_append($out, $bucket);
                break;
            }
        }

        return PSFS_PASS_ON;
    }

    /**
     * {@inheritDoc}
     * @see \php_user_filter::onCreate()
     */
    public function onCreate()
    {
        if (static::normalizerIsAvailable()) {
            $forms = static::getNormalizationForms();
            if ($this->filtername === static::$namespace) {
                $form = (int) $this->params;
                if (in_array($form, $forms, true)) {
                    $this->form = $form;

                    return true;
                }
            } elseif (strpos($this->filtername, '.') !== false) {
                list($namespace, $filter) = explode('.', $this->filtername, 2);
                if ($namespace === static::$namespace && isset($forms[$filter])) {
                    $this->form = $forms[$filter];

                    return true;
                }
            }
        }
        /* Some other normalize.* filter was asked for,
        report failure so that PHP will keep looking */
        return false;
    }

    /**
     * @param  string            $input
     * @return string|false|null
     */
    protected function normalize($input)
    {
        if (static::NFD_MAC === $this->form) {
            $result = \Normalizer::normalize($input, \Normalizer::NFD);
            if ($result !== null && $result !== false) {
                $result = iconv('utf-8', 'utf8-mac', $input);
            }
        } else {
            $result = \Normalizer::normalize($input, $this->form);
        }

        return $result === null ? false : $result;
    }

    /**
     * @param  mixed $byte
     * @return int
     */
    public static function getLengthOfCodePoint($byte)
    {
        $byte = ord($byte) & self::MASK_BYTE;
        if ($byte < self::SINGLE_BYTE) {
            return 1;
        } elseif ($byte < self::PAYLOAD_BYTE) {
            return 0;
        } elseif ($byte < self::DOUBLE_BYTE) {
            return 2;
        } elseif ($byte < self::TRIPLE_BYTE) {
            return 3;
        } elseif ($byte < self::QUAD_BYTE) {
            return 4;
        }

        return -1;
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
        if (static::$namespace !== null || !static::normalizerIsAvailable()) {
            return false;
        }
        $result = stream_filter_register($namespace, static::class);
        if ($result === true) {
            $result = stream_filter_register(sprintf('%s.*', $namespace), static::class);
        }
        if ($result === true) {
            static::$namespace = $namespace;
        }

        return $result;
    }

    const NFD_MAC = 32; // 0x2 & 0xF

    protected static $forms = null;

    protected static function setupForms()
    {
        if (static::$forms !== null) {
            return static::$forms;
        }
        static::$forms = array(
            // NONE
            'none' => \Normalizer::NONE,
            'binary' => \Normalizer::NONE,
            'validate' => \Normalizer::NONE,
            // NFC
            'c' => \Normalizer::NFC,
            'nfc' => \Normalizer::NFC,
            'form-c' => \Normalizer::NFC,
            'html5' => \Normalizer::NFC,
            'legacy' => \Normalizer::NFC,
            'compose' => \Normalizer::NFC,
            'recompose' => \Normalizer::NFC,
            // NFD
            'd' => \Normalizer::NFD,
            'nfd' => \Normalizer::NFD,
            'form-d' => \Normalizer::NFD,
            'decompose' => \Normalizer::NFD,
            'collation' => \Normalizer::NFD,
            // NFKC
            'kc' => \Normalizer::NFKC,
            'nfkc' => \Normalizer::NFKC,
            'form-kc' => \Normalizer::NFKC,
            'matching' => \Normalizer::NFKC,
            // NFKD
            'kd' => \Normalizer::NFKD,
            'nfkd' => \Normalizer::NFKD,
            'form-kd' => \Normalizer::NFKD,
        );
        if (static::macIconvIsAvailable()) {
            static::$forms = array_merge(
                static::$forms,
                array(
                    'mac' => static::NFD_MAC,
                    'd-mac' => static::NFD_MAC,
                    'nfd-mac' => static::NFD_MAC,
                    'form-d-mac' => static::NFD_MAC,
                )
            );
        }

        return static::$forms;
    }

    /**
     * Return true if all dependencies of the normalizer implementation are met
     *
     * @return bool
     */
    protected static function normalizerIsAvailable()
    {
        return class_exists('Normalizer', true);
    }

    /**
     * Return true if all dependencies of the iconv implementation are met
     *
     * @return bool
     */
    protected static function macIconvIsAvailable()
    {
        $nfc = hex2bin('64c3a96ac3a020ed9b88ec87bce284a2e2929ce4bda0');
        $mac = hex2bin('6465cc816a61cc8020e18492e185aee186abe18489e185ade284a2e2929ce4bda0');

        return (
            extension_loaded('iconv') &&
            $mac === @iconv('utf-8', 'utf-8-mac', $nfc) &&
            $nfc === @iconv('utf-8-mac', 'utf-8', $mac)
        );
    }
}
