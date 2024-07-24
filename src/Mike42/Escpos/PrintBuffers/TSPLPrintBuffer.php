<?php declare(strict_types=1);
/**
 * Created by PhpStorm.
 * User: Rutvij
 * Date: 3/28/2018
 * Time: 11:43 AM
 */
namespace Mike42\Escpos\PrintBuffers;

use LogicException;
use Mike42\Escpos\Printer;
use Mike42\Escpos\TSPLPrinter;

class TSPLPrintBuffer implements TSPLBuffer {

    /**
     * @var Printer $printer
     *  Printer for output
     */
    private $printer;

    /**
     * Empty print buffer.
     */
    public function __construct()
    {
        $this -> printer = null;
    }

    /**
     * Cause the buffer to send any partial input and wait on a newline.
     * If the printer is already on a new line, this does nothing.
     */
    public function flush()
    {
        if ($this -> printer == null) {
            throw new LogicException("Not attached to a printer.");
        }
    }

    /**
     * Used by Escpos to check if a printer is set.
     */
    public function getPrinter(): ?TSPLPrinter
    {
        return $this -> printer;
    }

    /**
     * Used by Escpos to hook up one-to-one link between buffers and printers.
     * @param TSPLPrinter $printer New printer
     */
    public function setPrinter(TSPLPrinter $printer = null)
    {
        $this -> printer = $printer;
    }

    /**
     * Accept UTF-8 text for printing.
     *
     * @param string $text Text to print
     */
    public function writeText(string $text)
    {
        if ($this -> printer == null) {
            throw new LogicException("Not attached to a printer.");
        }
        if ($text == null) {
            return;
        }
    }

    /**
     * Write data to the underlying printer.
     *
     * @param string $data
     */
    private function write(string $data)
    {
        $this -> printer -> getPrintConnector() -> write($data);
    }

}