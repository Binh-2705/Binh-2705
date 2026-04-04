<?php

require __DIR__ . '/../vendor/autoload.php';

use Smalot\PdfParser\Parser;

function docNoiDungCV($path){

$parser = new Parser();

$pdf = $parser->parseFile($path);

$text = $pdf->getText();

return $text;

}