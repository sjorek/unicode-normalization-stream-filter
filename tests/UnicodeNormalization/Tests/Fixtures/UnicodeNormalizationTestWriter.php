<?php
namespace Sjorek\UnicodeNormalization\Tests\Fixtures;

 /**
  * A file object to write "UnicodeNormalizationTest.X.Y.Z.txt" fixture files.
  *  
  * @author Stephan Jorek <stephan.jorek@gmail.com>
  */
class UnicodeNormalizationTestWriter extends \SplFileObject {

    const FIRST_LINE  = '# Generator : %s';
    const SECOND_LINE = '# Source    : %s';

    /**
     * @var string
     */
    public $filePath;

    /**
     * Constructor
     *
     * @param $unicodeVersion string
     * @param $sourceTemplate string
     */
    public function __construct($unicodeVersion, $generator, $source)
    {
        $destinationTemplate = __DIR__ . '/UnicodeNormalizationTest.%s.txt.gz';
        $this->filePath = sprintf($destinationTemplate, $unicodeVersion);
        parent::__construct('compress.zlib://' . $this->filePath, 'w', false);
        $this->add(sprintf(self::FIRST_LINE, $generator) . chr(10));
        $this->add(sprintf(self::SECOND_LINE, $source) . chr(10));
        $this->add('# --------------------------------------------------------------------------------' . chr(10));
    }

    /**
     * @param string $line
     */
    public function add($line)
    {
        if ($this->fwrite($line) === null) {
            throw new \Exception('Could not write "UnicodeNormalizationTest.X.Y.Z.txt.gz file.');
        }
    }
}
