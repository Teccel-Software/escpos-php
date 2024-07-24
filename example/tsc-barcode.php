<?php
/**
 * This print-out shows how large the available font sizes are. It is included
 * separately due to the amount of text it prints.
 *
 * @author Michael Billington <michael.billington@gmail.com>
 */
require __DIR__ . '/../vendor/autoload.php';
use Mike42\Escpos\Printer;
use Mike42\Escpos\PrintConnectors\FilePrintConnector;
use Mike42\Escpos\TSPLPrinter;

$connector = new FilePrintConnector("php://stdout");
$printer = new TSPLPrinter($connector, 38, 20, true);

$printer -> setText(
    "SIZE:32",
    25,
    68,
    '0',
    0,
    12,
    12,
    TSPLPrinter::JUSTIFY_LEFT
);

//---------------------- Column 2 ------------------------------
$printer -> setText(
    "Rs. 750",
    300,
    68,
    '0',
    0,
    12,
    12,
    TSPLPrinter::JUSTIFY_LEFT,
);

$printer -> setBarcode(
    "2024123030",
    33,
    304,
    TSPLPrinter::BARCODE_TYPE_128,
    20,
    TSPLPrinter::JUSTIFY_LEFT,
    0,
    2,
    4
);

$printer->setPrint();
$printer->close();