<?php declare(strict_types=1);
/**
 * Created by PhpStorm.
 * User: Rutvij
 * Date: 3/28/2018
 * Time: 11:46 AM
 */
namespace Mike42\Escpos;

use InvalidArgumentException;
use Mike42\Escpos\PrintBuffers\TSPLPrintBuffer;
use Mike42\Escpos\PrintConnectors\PrintConnector;
use Mike42\Escpos\PrintBuffers\PrintBuffer;
use Mike42\Escpos\PrintBuffers\TSPLBuffer;

class TSPLPrinter {
    /**
     * Align text to the left, when used with Printer::setJustification
     */
    const JUSTIFY_LEFT = 1;

    /**
     * Center text, when used with Printer::setJustification
     */
    const JUSTIFY_CENTER = 2;

    /**
     * Align text to the right, when used with Printer::setJustification
     */
    const JUSTIFY_RIGHT = 3;

    /**
     * Line Break
     */
    const LINE_BREAK = "\r\n";

    /**
     * Barcode Type 25
     */
    const BARCODE_TYPE_25 = "25";

    /**
     * Barcode Type 128
     */
    const BARCODE_TYPE_39 = "39";

    /**
     * Barcode Type 128
     */
    const BARCODE_TYPE_128 = "128";


    /**
     * @var PrintBuffer $buffer
     *  The printer's output buffer.
     */
    protected $buffer;

    /**
     * @var PrintConnector $connector
     *  Connector showing how to print to this printer
     */
    protected $connector;

    /**
     * Construct a new print object
     *
     * @param PrintConnector $connector The PrintConnector to send data to. If not set, output is sent to standard output.
     * @throws InvalidArgumentException
     */
    public function __construct(PrintConnector $connector, int $sizeX = 4,int $sizeY = 0, bool $inMM = false)
    {
        /* Set connector */
        $this -> connector = $connector;

        /* Set Size */
        $this->setSize($sizeX, $sizeY, $inMM);

        /* Set buffer */
        $this -> buffer = null;
        $this -> setPrintBuffer(new TSPLPrintBuffer());
        $this -> initialize();
    }

    /**
     * Close the underlying buffer. With some connectors, the
     * job will not actually be sent to the printer until this is called.
     */
    public function close()
    {
        $this -> connector -> finalize();
    }

    /**
     * @return TSPLBuffer
     */
    public function getPrintBuffer()
    {
        return $this -> buffer;
    }

    /**
     * @return PrintConnector
     */
    public function getPrintConnector()
    {
        return $this -> connector;
    }

    /**
     * Initialize printer. This resets formatting back to the defaults.
     */
    public function initialize()
    {
        $this -> connector -> write("DIRECTION 1".self::LINE_BREAK);
        $this -> connector -> write("CLS".self::LINE_BREAK);
    }

    /**
     * Set Gap between two labels
     * By default printer treat as continuous label
     * @param int $gapX
     * @param int $gapY
     * @param bool $inMM
     */
    public function setGap(int $gapX = 0,int $gapY = 0 ,bool $inMM = false) {
        self::validateInteger($gapX, 0, 255, __FUNCTION__);
        self::validateInteger($gapY, 0, 255, __FUNCTION__);
        $this -> connector -> write("GAP ".$gapX."".($inMM ? " MM ":"").",".$gapY."".($inMM ? " MM " : "").self::LINE_BREAK);
    }

    /**
     * Attach a different print buffer to the printer. Buffers are responsible for handling text output to the printer.
     *
     * @param TSPLBuffer $buffer The buffer to use.
     * @throws InvalidArgumentException Where the buffer is already attached to a different printer.
     */
    public function setPrintBuffer(TSPLBuffer $buffer)
    {
        if ($buffer === $this -> buffer) {
            return;
        }
        if ($buffer -> getPrinter() != null) {
            throw new InvalidArgumentException("This buffer is already attached to a printer.");
        }
        if ($this -> buffer !== null) {
            $this -> buffer -> setPrinter(null);
        }
        $this -> buffer = $buffer;
        $this -> buffer -> setPrinter($this);
    }

    /**
     * Attach a size of the label which can be used to print a label on it.
     * @param int $sizeX - Horizontal length of label
     * @param int $sizeY - Vertical length of label
     * @param bool $inMM - Vertical length of label
     * @throws InvalidArgumentException Where the buffer is already attached to a different printer.
     */
    public function setSize(int $sizeX, int $sizeY, bool $inMM = false) {
        self::validateInteger($sizeX, 1, 100, __FUNCTION__);
        self::validateInteger($sizeY, 1, 100, __FUNCTION__);
        $formattedSizeX = (string) $sizeX."".($inMM ? " mm": "");
        $formattedSizeY = (string) $sizeY."".($inMM ? " mm": "");
        $this->connector->write("SIZE {$formattedSizeX} {$formattedSizeY}".self::LINE_BREAK);
    }

    /**
     * @param $text
     * @param int $x
     * @param int $y
     * @param int $font
     * @param int $rotation
     * @param int $horizontalMultiplication
     * @param int $verticalMultiplication
     * @param int $alignment
     */
    public function setText(string $text, int $x = 10, int $y = 10, int $font = 0, int $rotation = 0, int $horizontalMultiplication = 1, int $verticalMultiplication = 1,int $alignment = TSPLPrinter::JUSTIFY_LEFT)
    {
        self::validateInteger($horizontalMultiplication, 1, 15, __FUNCTION__);
        self::validateInteger($verticalMultiplication, 1, 15, __FUNCTION__);
        $this -> connector -> write('TEXT '
            . $x . ','
            . $y . ',"'
            . strval($font) .'",'
            .$rotation .','
            .$horizontalMultiplication .','
            .$verticalMultiplication .','
            .$alignment .','
            .'"'.strval($text) .'"'
            .self::LINE_BREAK);
    }

    /**
     * Add Barcode string in the file.
     * @param string $barcodeText
     * @param int $x
     * @param int $y
     * @param string $codeType
     * @param int $height
     * @param int $humanReadable
     * @param int $rotation
     * @param int $narrow
     * @param int $wide
     */
    public function setBarcode(string $barcodeText, int $x = 10, int $y = 10, string $codeType = TSPLPrinter::BARCODE_TYPE_128, int $height = 50, int $humanReadable = 0, int $rotation = 0, int $narrow = 1, int $wide = 1) {
        self::validateInteger($x, 1, 350, __FUNCTION__);
        self::validateInteger($y, 1, 10000, __FUNCTION__);
        self::validateInteger($height, 1, 100, __FUNCTION__);
        self::validateInteger($rotation, 0, 361, __FUNCTION__);
        self::validateInteger($narrow, 1, 10, __FUNCTION__);
        self::validateInteger($wide, 1, 10, __FUNCTION__);
        $this -> connector -> write('BARCODE '
            .$x. ','
            .$y.','
            .'"'.$codeType.'",'
            .$height.','
            .$humanReadable.','
            .$rotation.','
            .$narrow.','
            .$wide.','
            .' "'.strval($barcodeText).'"'
            .SELF::LINE_BREAK);
    }

    /**
     * Print command will print the label format currently stored in the image buffer.
     * @param int $noOfSet
     * @param int $noOfCopy
     */
    public function setPrint(int $noOfSet = 1, int $noOfCopy = 1) {
        self::validateInteger($noOfSet, 1, 10, __FUNCTION__);
        self::validateInteger($noOfCopy, 1, 10, __FUNCTION__);
        $this -> connector ->write("PRINT ".$noOfSet .",".$noOfCopy."".SELF::LINE_BREAK);
        $this -> connector ->write("EOP");
    }

    protected static function validateInteger(int $test, int $min, int $max, string $source, string $argument = "Argument")
    {
        self::validateIntegerMulti($test, [[$min, $max]], $source, $argument);
    }

    /**
     * Throw an exception if the argument given is not an integer within one of the specified ranges
     *
     * @param int $test the input to test
     * @param array $ranges array of two-item min/max ranges.
     * @param string $source the name of the function calling this
     * @param string $source the name of the function calling this
     * @param string $argument the name of the invalid parameter
     */
    protected static function validateIntegerMulti(int $test, array $ranges, string $source, string $argument = "Argument")
    {
        if (!is_integer($test)) {
            throw new InvalidArgumentException("$argument given to $source must be a number, but '$test' was given.");
        }
        $match = false;
        foreach ($ranges as $range) {
            $match |= $test >= $range[0] && $test <= $range[1];
        }
        if (!$match) {
            // Put together a good error "range 1-2 or 4-6"
            $rangeStr = "range ";
            for ($i = 0; $i < count($ranges); $i++) {
                $rangeStr .= $ranges[$i][0] . "-" . $ranges[$i][1];
                if ($i == count($ranges) - 1) {
                    continue;
                } elseif ($i == count($ranges) - 2) {
                    $rangeStr .= " or ";
                } else {
                    $rangeStr .= ", ";
                }
            }
            throw new InvalidArgumentException("$argument given to $source must be in $rangeStr, but $test was given.");
        }
    }
}