<?php

/*
 * This file is part of Unicode Normalization Stream Filter.
 *
 * (c) Stephan Jorek <stephan.jorek@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sjorek\UnicodeNormalization\Tests;

use PHPUnit\Framework\TestCase;
use Sjorek\UnicodeNormalization\StreamFilter;

/**
 * @author Stephan Jorek <stephan.jorek@gmail.com>
 */
class StreamFilterTest extends TestCase
{
    /**
     * This method is called before the first test of this test class is run.
     */
    public static function setUpBeforeClass()
    {
        StreamFilter::register();
    }

    public function provideCheckGetLengthOfCodePointData()
    {
        // aä€𐍈
        $string = hex2bin('61c3a4e282acf0908d88');

        return array(
            'single byte' => array(1, $string[0]),
            'double byte opener' => array(2, $string[1]),
            'double byte payload 1' => array(0, $string[2]),
            'triple byte opener' => array(3, $string[3]),
            'triple byte payload 1' => array(0, $string[4]),
            'triple byte payload 2' => array(0, $string[5]),
            'quad byte opener' => array(4, $string[6]),
            'quad byte payload 1' => array(0, $string[7]),
            'quad byte payload 2' => array(0, $string[8]),
            'quad byte payload 3' => array(0, $string[9]),
        );
    }

    /**
     * @test
     * @dataProvider provideCheckGetLengthOfCodePointData
     *
     * @param int    $expected
     * @param string $byte
     */
    public function checkGetLengthOfCodePoint($expected, $byte)
    {
        $this->assertSame($expected, StreamFilter::getLengthOfCodePoint($byte));
    }
}
