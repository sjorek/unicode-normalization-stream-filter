<?php declare(strict_types=1);

/*
 * This file is part of Unicode Normalization Stream Filter.
 *
 * (c) Stephan Jorek <stephan.jorek@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sjorek\UnicodeNormalization;

if (getenv('UNICODE_NORMALIZER_IMPLEMENTATION') !== false) {
    class_alias(getenv('UNICODE_NORMALIZER_IMPLEMENTATION'), __NAMESPACE__ . '\\Normalizer', true);
} elseif (class_exists('Normalizer', true)) {
    // class_alias only works for user defined classes
    class Normalizer extends \Normalizer
    {
    }
}
